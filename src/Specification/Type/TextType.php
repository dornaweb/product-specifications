<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use WC_Product;

final class TextType implements SpecificationType
{
    public const ID = 'text';

    public function id(): string
    {
        return self::ID;
    }

    public function label(): string
    {
        return __('Text', 'product-specifications');
    }

    /**
     * @return array<string, mixed>
     */
    public function optionsSchema(): array
    {
        return OptionsSchema::none();
    }

    /**
     * This type is the registry's fallback, so it is handed values belonging to
     * types it knows nothing about. Anything but a scalar is left alone rather
     * than flattened into a string.
     */
    public function sanitize(mixed $input): mixed
    {
        if (!is_scalar($input)) {
            return $input;
        }

        $sanitized = sanitize_text_field((string) $input);

        return $sanitized === '' ? null : $sanitized;
    }

    public function value(WC_Product $product, mixed $stored): mixed
    {
        return is_scalar($stored) ? (string) $stored : null;
    }

    public function render(mixed $value, SpecificationOptions $options): string
    {
        return is_scalar($value) ? esc_html((string) $value) : '';
    }
}
