<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Deletion;

use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\SpecificationTaxonomy;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSet;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetRepository;
use RuntimeException;

/**
 * Products keep a deleted specification's values until each is next saved. The storefront
 * never shows a value whose specification is gone, so products are not rewritten here.
 */
final class SpecificationDeletionService
{
    public function __construct(
        private readonly SpecificationDefinitionRepository $definitions,
        private readonly SpecificationSetRepository $sets
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

        if ($this->definitions->find((int) $termId) === null) {
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
