<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Storefront;

use Amiut\ProductSpecs\ProductValues\ProductValueRepository;
use Amiut\ProductSpecs\Specification\SpecificationDefinition;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use Amiut\ProductSpecs\SpecificationSet\SpecificationGroup;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetRepository;
use Amiut\ProductSpecs\Migration\MigrationState;
use WC_Product;

final class SpecificationsTableReader
{
    /**
     * @var array<int, SpecificationsTable>
     */
    private array $tables = [];

    public function __construct(
        private readonly ProductValueRepository $values,
        private readonly SpecificationSetRepository $sets,
        private readonly SpecificationDefinitionRepository $definitions,
        private readonly SpecificationTypeRegistry $types,
        private readonly MigrationState $migration
    ) {
    }

    public function forProduct(WC_Product $product): SpecificationsTable
    {
        return $this->tables[$product->get_id()] ??= $this->read($product);
    }

    private function read(WC_Product $product): SpecificationsTable
    {
        if ($this->migration->requiresMigration()) {
            return SpecificationsTable::empty();
        }

        $setSlug = $this->values->assignedSet($product->get_id());
        $set = $setSlug === null ? null : $this->sets->bySlug($setSlug);

        if ($set === null || !$set->isPublished()) {
            return SpecificationsTable::empty();
        }

        $values = $this->values->read($product->get_id())->values();
        $definitions = $this->definitions->findByKeys($this->visibleKeys($set->structure()->groups()));
        $groups = [];

        foreach ($set->structure()->groups() as $group) {
            $rows = $group->visible() ? $this->rows($group, $product, $values, $definitions) : [];

            if ($rows !== []) {
                $groups[] = new TableGroup($group->key(), $group->label(), $rows);
            }
        }

        return new SpecificationsTable($groups);
    }

    /**
     * @param list<SpecificationGroup> $groups
     * @return list<string>
     */
    private function visibleKeys(array $groups): array
    {
        $keys = [];

        foreach ($groups as $group) {
            if (!$group->visible()) {
                continue;
            }

            foreach ($group->items() as $item) {
                if ($item->visible()) {
                    $keys[] = $item->slug();
                }
            }
        }

        return array_values(array_unique($keys));
    }

    /**
     * @param array<string, mixed> $values
     * @param array<string, SpecificationDefinition> $definitions
     * @return list<TableRow>
     */
    private function rows(SpecificationGroup $group, WC_Product $product, array $values, array $definitions): array
    {
        $rows = [];

        foreach ($group->items() as $item) {
            $definition = $item->visible() ? ($definitions[$item->slug()] ?? null) : null;

            if ($definition === null || !$this->types->has($definition->typeId())) {
                continue;
            }

            $type = $this->types->get($definition->typeId());
            $value = $type->value($product, $values[$definition->key()] ?? null, $definition->options());

            if ($value === null || $value === '') {
                continue;
            }

            $output = $type->render($value, $definition->options());

            if ($output !== '') {
                $rows[] = new TableRow($definition->key(), $definition->name(), $output);
            }
        }

        return $rows;
    }
}
