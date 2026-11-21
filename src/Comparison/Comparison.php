<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Comparison;

use Amiut\ProductSpecs\SpecificationSet\SpecificationSet;
use WC_Product;

final class Comparison
{
    /**
     * @param list<WC_Product> $products
     * @param list<ComparisonGroup> $groups
     */
    public function __construct(
        private readonly ?SpecificationSet $set,
        private readonly array $products,
        private readonly array $groups
    ) {
    }

    public static function empty(): self
    {
        return new self(null, [], []);
    }

    public function isEmpty(): bool
    {
        return $this->products === [];
    }

    /**
     * @return list<WC_Product>
     */
    public function products(): array
    {
        return $this->products;
    }

    public function ids(): ComparedProductIds
    {
        return ComparedProductIds::of(
            array_map(static fn (WC_Product $product): int => $product->get_id(), $this->products)
        );
    }

    public function setSlug(): string
    {
        return $this->set?->slug() ?? '';
    }

    /**
     * @return list<ComparisonGroup>
     */
    public function groups(): array
    {
        return $this->groups;
    }
}
