<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Storefront;

use Amiut\ProductSpecs\Migration\MigrationState;
use Amiut\ProductSpecs\ProductValues\ProductValueRepository;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use WC_Product;

/**
 * What a product's specifications are, as the storefront shows them. The public API
 * reads through here.
 */
final class ProductSpecifications
{
    public function __construct(
        private readonly SpecificationsTableReader $tables,
        private readonly ProductValueRepository $values,
        private readonly SpecificationDefinitionRepository $definitions,
        private readonly SpecificationTypeRegistry $types,
        private readonly MigrationState $migration
    ) {
    }

    public function table(int|WC_Product $product): SpecificationsTable
    {
        $product = $this->product($product);

        return $product === null ? SpecificationsTable::empty() : $this->tables->forProduct($product);
    }

    public function has(int|WC_Product $product): bool
    {
        return !$this->table($product)->isEmpty();
    }

    /**
     * The value as the specification's type reads it, such as a boolean for a yes-or-no
     * specification. Null when the product holds none.
     */
    public function value(int|WC_Product $product, string $specificationKey): mixed
    {
        $product = $this->product($product);
        $definition = $this->definitions->findByKey($specificationKey);

        if ($product === null || $definition === null || $this->migration->requiresMigration()) {
            return null;
        }

        if (!$this->types->has($definition->typeId())) {
            return null;
        }

        $stored = $this->values->read($product->get_id())->values()[$definition->key()] ?? null;

        return $this->types->get($definition->typeId())->value($product, $stored);
    }

    private function product(int|WC_Product $product): ?WC_Product
    {
        if ($product instanceof WC_Product) {
            return $product;
        }

        $found = $product > 0 ? wc_get_product($product) : null;

        return $found instanceof WC_Product ? $found : null;
    }
}
