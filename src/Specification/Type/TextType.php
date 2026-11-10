<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use WC_Product;

final class TextType implements SpecificationType
{
    public const ID = 'text';

    private const SEPARATOR = ', ';

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
        return TextOptions::schema();
    }

    /**
     * This type is the registry's fallback, so it is handed values belonging to
     * types it knows nothing about. Unless several values are allowed, anything but
     * a scalar is left alone rather than flattened into a string.
     */
    public function sanitize(mixed $input, SpecificationOptions $options): mixed
    {
        if (TextOptions::of($options)->allowsSeveral()) {
            return $this->sanitizedList($input);
        }

        if (!is_scalar($input)) {
            return $input;
        }

        $sanitized = sanitize_text_field((string) $input);

        return $sanitized === '' ? null : $sanitized;
    }

    public function validate(mixed $input, SpecificationOptions $options): ?string
    {
        return null;
    }

    /**
     * A list stays a list whatever the options say, so switching several values off
     * and on again rewrites nothing.
     */
    public function value(WC_Product $product, mixed $stored, SpecificationOptions $options): mixed
    {
        $items = self::items($stored);

        if ($items !== null) {
            return $items === [] ? null : $items;
        }

        if (!is_scalar($stored)) {
            return null;
        }

        return TextOptions::of($options)->allowsSeveral() ? [(string) $stored] : (string) $stored;
    }

    public function plainValue(mixed $value, SpecificationOptions $options): string
    {
        $items = self::items($value);

        if ($items !== null) {
            return implode(self::SEPARATOR, $items);
        }

        return is_scalar($value) ? (string) $value : '';
    }

    public function render(mixed $value, SpecificationOptions $options): string
    {
        return esc_html($this->plainValue($value, $options));
    }

    /**
     * @return list<string>|null
     */
    private static function items(mixed $value): ?array
    {
        if (!is_array($value) || !array_is_list($value)) {
            return null;
        }

        $items = [];

        foreach ($value as $item) {
            if (!is_string($item)) {
                return null;
            }

            $items[] = $item;
        }

        return $items;
    }

    /**
     * @return list<string>|null
     */
    private function sanitizedList(mixed $input): ?array
    {
        $candidates = is_array($input) ? $input : [$input];
        $items = [];

        foreach ($candidates as $candidate) {
            if (!is_scalar($candidate)) {
                continue;
            }

            $item = sanitize_text_field((string) $candidate);
            $key = ValueKey::of($item);

            if ($key !== '' && !array_key_exists($key, $items)) {
                $items[$key] = $item;
            }
        }

        return $items === [] ? null : array_values($items);
    }
}
