<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
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

    public function sanitize(mixed $input, SpecificationOptions $options): mixed
    {
        if (!is_scalar($input)) {
            return null;
        }

        $sanitized = sanitize_text_field((string) $input);

        return $sanitized === '' ? null : $sanitized;
    }

    public function validate(mixed $input, SpecificationOptions $options): ?string
    {
        $choice = $this->sanitize($input, $options);

        if ($choice === null || in_array($choice, array_map('sanitize_text_field', $options->choices()), true)) {
            return null;
        }

        return __('Choose one of the options.', 'product-specifications');
    }

    public function value(WC_Product $product, mixed $stored, SpecificationOptions $options): mixed
    {
        return is_scalar($stored) ? (string) $stored : null;
    }

    public function plainValue(mixed $value, SpecificationOptions $options): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    public function render(mixed $value, SpecificationOptions $options): string
    {
        return esc_html($this->plainValue($value, $options));
    }
}
