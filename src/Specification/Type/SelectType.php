<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type;

use WC_Product;

final class SelectType implements SpecificationType
{
    public const ID = 'select';

    public function id(): string
    {
        return self::ID;
    }

    public function label(): string
    {
        return __('Single select', 'product-specifications');
    }

    /**
     * @return array<string, mixed>
     */
    public function optionsSchema(): array
    {
        return OptionsSchema::choices();
    }

    public function sanitize(mixed $input): mixed
    {
        if (!is_scalar($input)) {
            return null;
        }

        $sanitized = sanitize_text_field((string) $input);

        return $sanitized === '' ? null : $sanitized;
    }

    public function value(WC_Product $product, mixed $stored): mixed
    {
        return is_scalar($stored) ? (string) $stored : null;
    }

    public function render(mixed $value): string
    {
        return is_scalar($value) ? esc_html((string) $value) : '';
    }
}
