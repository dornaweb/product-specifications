<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ProductValues;

final class ProductValueDocument
{
    public const CURRENT_VERSION = 1;

    /**
     * @param array<string, mixed> $values
     */
    private function __construct(private readonly array $values)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public static function fromStorage(mixed $stored): self
    {
        if (!is_array($stored)) {
            return self::empty();
        }

        $values = $stored['values'] ?? null;

        if (!is_array($values)) {
            return self::empty();
        }

        return self::fromValues($values);
    }

    /**
     * @param array<array-key, mixed> $values
     */
    public static function fromValues(array $values): self
    {
        $normalized = [];

        foreach ($values as $key => $value) {
            if (is_string($key) && $key !== '') {
                $normalized[$key] = $value;
            }
        }

        return new self($normalized);
    }

    /**
     * @return array<string, mixed>
     */
    public function values(): array
    {
        return $this->values;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->values);
    }

    /**
     * @return array{schema_version: int, values: array<string, mixed>}
     */
    public function toStorage(): array
    {
        return [
            'schema_version' => self::CURRENT_VERSION,
            'values' => $this->values,
        ];
    }
}
