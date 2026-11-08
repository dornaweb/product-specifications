<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
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

    public function sanitize(mixed $input, SpecificationOptions $options): mixed
    {
        if (!is_scalar($input)) {
            return null;
        }

        $sanitized = sanitize_textarea_field((string) $input);

        return $sanitized === '' ? null : $sanitized;
    }

    public function validate(mixed $input, SpecificationOptions $options): ?string
    {
        return null;
    }

    public function value(WC_Product $product, mixed $stored): mixed
    {
        return is_scalar($stored) ? (string) $stored : null;
    }

    public function plainValue(mixed $value, SpecificationOptions $options): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    public function render(mixed $value, SpecificationOptions $options): string
    {
        return nl2br(esc_html($this->plainValue($value, $options)));
    }
}
