<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Type\WooCommerce;

use WC_Product_Variation;

// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- WooCommerce owns these names.
final class FakeVariation extends WC_Product_Variation
{
    /**
     * @param array<string, string> $attributes The options this variation was created for.
     */
    public function __construct(int $id, int $parentId, private readonly array $attributes = [])
    {
        parent::__construct($id, $parentId);
    }

    public function get_attribute(string $attribute): string
    {
        return $this->attributes[$attribute] ?? '';
    }
}
