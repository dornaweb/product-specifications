<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\WooCommerce;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use Amiut\ProductSpecs\Specification\Type\SpecificationType;
use WC_Product;

final class WooCommerceType implements SpecificationType
{
    public const ID = 'woocommerce';

    public function __construct(private readonly ProductDataSources $sources)
    {
    }

    public function id(): string
    {
        return self::ID;
    }

    public function label(): string
    {
        return __('WooCommerce product data', 'product-specifications');
    }

    /**
     * @return array<string, mixed>
     */
    public function optionsSchema(): array
    {
        return SourceOptions::schema($this->sources->fixedIds());
    }

    /**
     * Nothing is entered for this type, so what arrives was stored under an earlier type.
     * It is kept, so that changing the type back restores it.
     */
    public function sanitize(mixed $input, SpecificationOptions $options): mixed
    {
        return $input;
    }

    public function validate(mixed $input, SpecificationOptions $options): ?string
    {
        return null;
    }

    public function value(WC_Product $product, mixed $stored, SpecificationOptions $options): mixed
    {
        return $this->sources->source($options)?->value($product);
    }

    public function plainValue(mixed $value, SpecificationOptions $options): string
    {
        return $this->sources->source($options)?->plainValue($value) ?? '';
    }

    public function render(mixed $value, SpecificationOptions $options): string
    {
        return $this->sources->source($options)?->render($value) ?? '';
    }
}
