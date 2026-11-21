<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Comparison;

use Amiut\ProductSpecs\Migration\MigrationState;
use Amiut\ProductSpecs\ProductValues\ProductValueRepository;
use Amiut\ProductSpecs\Specification\SpecificationDefinition;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\SpecificationSet\SpecificationGroup;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSet;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetRepository;
use Amiut\ProductSpecs\Storefront\ValueRenderer;
use WC_Product;

final class ComparisonReader
{
    public function __construct(
        private readonly ProductCatalog $catalog,
        private readonly ProductValueRepository $values,
        private readonly SpecificationSetRepository $sets,
        private readonly SpecificationDefinitionRepository $definitions,
        private readonly ValueRenderer $renderer,
        private readonly MigrationState $migration
    ) {
    }

    public function read(ComparedProductIds $ids, int $maximum): Comparison
    {
        if ($ids->isEmpty() || $this->migration->requiresMigration()) {
            return Comparison::empty();
        }

        $set = null;
        $products = [];

        foreach ($this->catalog->comparableProducts($ids->ids()) as $product) {
            $setSlug = $this->values->assignedSet($product->get_id());
            $set ??= $this->comparableSet($setSlug);

            if ($set !== null && $setSlug === $set->slug()) {
                $products[] = $product;
            }
        }

        if ($set === null || $products === []) {
            return Comparison::empty();
        }

        $products = array_slice($products, 0, $maximum);

        return new Comparison($set, $products, $this->groups($set, $products));
    }

    private function comparableSet(?string $slug): ?SpecificationSet
    {
        $set = $slug === null ? null : $this->sets->bySlug($slug);

        return $set !== null && $set->isPublished() && $set->offersComparison() ? $set : null;
    }

    /**
     * @param list<WC_Product> $products
     * @return list<ComparisonGroup>
     */
    private function groups(SpecificationSet $set, array $products): array
    {
        $definitions = $this->definitions->findByKeys($set->structure()->visibleKeys());
        $values = [];

        foreach ($products as $product) {
            $values[] = $this->values->read($product->get_id())->values();
        }

        $groups = [];

        foreach ($set->structure()->groups() as $group) {
            $rows = $group->visible() ? $this->rows($group, $products, $values, $definitions) : [];

            if ($rows !== []) {
                $groups[] = new ComparisonGroup($group->label(), $rows);
            }
        }

        return $groups;
    }

    /**
     * @param list<WC_Product> $products
     * @param list<array<string, mixed>> $values
     * @param array<string, SpecificationDefinition> $definitions
     * @return list<ComparisonRow>
     */
    private function rows(SpecificationGroup $group, array $products, array $values, array $definitions): array
    {
        $rows = [];

        foreach ($group->items() as $item) {
            $definition = $item->visible() ? ($definitions[$item->slug()] ?? null) : null;

            if ($definition === null) {
                continue;
            }

            $cells = [];

            foreach ($products as $index => $product) {
                $cells[] = $this->renderer->render($product, $definition, $values[$index][$definition->key()] ?? null);
            }

            if (array_filter($cells, static fn (?string $cell): bool => $cell !== null) !== []) {
                $rows[] = new ComparisonRow($definition->name(), $cells);
            }
        }

        return $rows;
    }
}
