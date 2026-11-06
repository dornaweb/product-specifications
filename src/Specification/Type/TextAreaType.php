<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type;

use WC_Product;

final class TextAreaType implements SpecificationType
{
    public const ID = 'textarea';

    public function id(): string
    {
        return self::ID;
    }

    public function label(): string
    {
        return __('Paragraph', 'product-specifications');
    }

    /**
     * @return array<string, mixed>
     */
    public function optionsSchema(): array
    {
        return OptionsSchema::none();
    }

    public function sanitize(mixed $input): mixed
    {
        if (!is_scalar($input)) {
            return null;
        }

        $sanitized = sanitize_textarea_field((string) $input);

        return $sanitized === '' ? null : $sanitized;
    }

    public function value(WC_Product $product, mixed $stored): mixed
    {
        return is_scalar($stored) ? (string) $stored : null;
    }

    public function render(mixed $value): string
    {
        return is_scalar($value) ? nl2br(esc_html((string) $value)) : '';
    }
}
