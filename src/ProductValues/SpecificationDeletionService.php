<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ProductValues;

use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\SpecificationTaxonomy;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetRepository;
use RuntimeException;

final class SpecificationDeletionService
{
    public function __construct(
        private readonly SpecificationDefinitionRepository $definitions,
        private readonly SpecificationSetRepository $sets,
        private readonly ProductValueRepository $values
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

        $cleanedSets = [];

        foreach ($this->sets->all() as $set) {
            $structure = $set->structure();
            $cleaned = $structure->withoutItemEverywhere((int) $termId);

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

        if (!$this->values->purge($definition->key())) {
            $this->revert($cleanedSets);
            $this->fail(
                __('its product values could not be cleaned up', 'product-specifications')
            );
        }
    }

    /**
     * @param list<\Amiut\ProductSpecs\SpecificationSet\SpecificationSet> $originalSets
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
