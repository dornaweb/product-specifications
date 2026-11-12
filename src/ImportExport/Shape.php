<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport;

final class Shape
{
    public const SLUG_PATTERN = '/^[a-z0-9_%-]+$/';
    public const KEY_PATTERN = '/^[a-z0-9_-]+$/';

    /**
     * @param array<array-key, mixed> $entry
     * @return list<string>
     */
    public static function text(array $entry, string $field, string $path, bool $required): array
    {
        if (!array_key_exists($field, $entry)) {
            return $required ? [self::error("{$path}.{$field}", __('is required', 'product-specifications'))] : [];
        }

        if (!is_string($entry[$field])) {
            return [self::error("{$path}.{$field}", __('must be text', 'product-specifications'))];
        }

        if ($required && trim($entry[$field]) === '') {
            return [self::error("{$path}.{$field}", __('must not be empty', 'product-specifications'))];
        }

        return [];
    }

    /**
     * @param array<array-key, mixed> $entry
     * @return list<string>
     */
    public static function identifier(array $entry, string $field, string $path, string $pattern): array
    {
        $errors = self::text($entry, $field, $path, true);

        if ($errors === [] && preg_match($pattern, self::string($entry, $field)) !== 1) {
            $errors[] = self::error(
                "{$path}.{$field}",
                __('may contain only lowercase letters, digits, dashes and underscores', 'product-specifications')
            );
        }

        return $errors;
    }

    /**
     * @param array<array-key, mixed> $entry
     * @return list<string>
     */
    public static function boolean(array $entry, string $field, string $path): array
    {
        if (!array_key_exists($field, $entry) || is_bool($entry[$field])) {
            return [];
        }

        return [self::error("{$path}.{$field}", __('must be true or false', 'product-specifications'))];
    }

    /**
     * @param array<array-key, mixed> $entry
     * @return list<string>
     */
    public static function list(array $entry, string $field, string $path, bool $required): array
    {
        if (!array_key_exists($field, $entry)) {
            return $required ? [self::error("{$path}.{$field}", __('is required', 'product-specifications'))] : [];
        }

        if (!is_array($entry[$field]) || !array_is_list($entry[$field])) {
            return [self::error("{$path}.{$field}", __('must be a list', 'product-specifications'))];
        }

        return [];
    }

    /**
     * @param array<array-key, mixed> $entry
     */
    public static function string(array $entry, string $field): string
    {
        return is_string($entry[$field] ?? null) ? $entry[$field] : '';
    }

    public static function error(string $path, string $problem): string
    {
        return sprintf('%1$s: %2$s', $path, $problem);
    }

    public static function duplicate(string $path): string
    {
        return self::error($path, __('is a duplicate', 'product-specifications'));
    }
}
