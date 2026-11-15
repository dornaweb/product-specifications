<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Type\WooCommerce;

use WC_Product;

// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- WooCommerce owns these names.
final class FakeProduct extends WC_Product
{
    /**
     * @param array<string, string> $fields
     * @param array<string, string> $attributes
     */
    public function __construct(
        int $id = 1,
        private readonly array $fields = [],
        private readonly bool $virtual = false,
        private readonly array $attributes = []
    ) {
        parent::__construct($id);
    }

    public function get_weight(): string
    {
        return $this->fields['weight'] ?? '';
    }

    public function get_length(): string
    {
        return $this->fields['length'] ?? '';
    }

    public function get_width(): string
    {
        return $this->fields['width'] ?? '';
    }

    public function get_height(): string
    {
        return $this->fields['height'] ?? '';
    }

    public function is_virtual(): bool
    {
        return $this->virtual;
    }

    public function has_weight(): bool
    {
        return $this->get_weight() !== '' && !$this->virtual;
    }

    public function has_dimensions(): bool
    {
        return ($this->get_length() !== '' || $this->get_width() !== '' || $this->get_height() !== '')
            && !$this->virtual;
    }

    public function get_attribute(string $attribute): string
    {
        return $this->attributes[$attribute] ?? '';
    }
}
