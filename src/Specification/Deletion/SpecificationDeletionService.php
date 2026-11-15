<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Deletion;

use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\SpecificationTaxonomy;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSet;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetRepository;
use RuntimeException;

/**
 * Deleting a specification removes it from every set and its value from every product,
 * so a later specification given the same key finds nothing to adopt. Up to SYNC_LIMIT
 * products are rewritten inside the deletion; the rest, and anything a failed write
 * leaves behind, are queued for the background, and the key counts as taken until the
 * queue has finished with it. Only a set that cannot be saved stops the deletion, and
 * that happens before any product is touched.
 */
final class SpecificationDeletionService
{
    public const SYNC_LIMIT = 200;

    public function __construct(
        private readonly SpecificationDefinitionRepository $definitions,
        private readonly SpecificationSetRepository $sets,
        private readonly ValuePurge $purge,
        private readonly PurgeQueue $queue,
        private readonly BackgroundPurge $background
    ) {
    }

    public function register(): void
    {
        add_action('pre_delete_term', [$this, 'beforeDelete'], 10, 2);
    }

    public function beforeDelete(int|string $termId, string $taxonomy): void
    {
        if ($taxonomy !== SpecificationTaxonomy::KEY || !is_numeric($termId)) {
            return;
        }

        $definition = $this->definitions->find((int) $termId);

        if ($definition === null) {
            return;
        }

        $this->cleanSets((int) $termId);

        $progress = $this->purge->purge($definition->key(), 0, self::SYNC_LIMIT);

        if ($progress->isFinished()) {
            return;
        }

        $this->queue->add($definition->key(), $progress->cursor());

        if ($progress->failure() !== null) {
            $this->queue->recordFailure($definition->key(), $progress->failure());
        }

        $this->background->schedule();
    }

    private function cleanSets(int $termId): void
    {
        $cleanedSets = [];

        foreach ($this->sets->all() as $set) {
            $structure = $set->structure();
            $cleaned = $structure->withoutItemEverywhere($termId);

            if ($cleaned->groups() === $structure->groups()) {
                continue;
            }

            if ($this->sets->save($set->withStructure($cleaned)) < 1) {
                $this->revert($cleanedSets);
                $this->fail(
                    __('its specification sets could not be updated', 'product-specifications')
                );
            }

            $cleanedSets[] = $set;
        }
    }

    /**
     * @param list<SpecificationSet> $originalSets
     */
    private function revert(array $originalSets): void
    {
        foreach ($originalSets as $set) {
            $this->sets->save($set);
        }
    }

    private function fail(string $reason): never
    {
        throw new RuntimeException(
            sprintf(
                __('The specification was not deleted because %s.', 'product-specifications'),
                $reason
            )
        );
    }
}
