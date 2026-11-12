<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification;

final class SpecificationOptions
{
    public const CHOICES_KEY = 'values';

    /**
     * @param array<string, mixed> $map
     */
    private function __construct(private readonly array $map)
    {
    }

    /**
     * @param array<string, mixed> $map
     */
    public static function fromMap(array $map): self
    {
        return new self(self::normalizeChoices($map));
    }

    public static function fromStorage(mixed $stored): self
    {
        if (!is_array($stored)) {
            return new self([]);
        }

        return self::fromMap(array_filter($stored, 'is_string', ARRAY_FILTER_USE_KEY));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->map;
    }

    /**
     * @return list<string>
     */
    public function choices(): array
    {
        return self::stringList($this->map[self::CHOICES_KEY] ?? []);
    }

    public function isEmpty(): bool
    {
        return $this->map === [];
    }

    /**
     * @param array<string, mixed> $map
     * @return array<string, mixed>
     */
    private static function normalizeChoices(array $map): array
    {
        if (!array_key_exists(self::CHOICES_KEY, $map)) {
            return $map;
        }

        $map[self::CHOICES_KEY] = self::stringList($map[self::CHOICES_KEY]);

        return $map;
    }

    /**
     * @return list<string>
     */
    private static function stringList(mixed $values): array
    {
        if (!is_array($values)) {
            return [];
        }

        return array_values(
            array_filter(
                array_map(
                    static fn (mixed $value): string => is_scalar($value) ? (string) $value : '',
                    $values
                ),
                static fn (string $value): bool => $value !== ''
            )
        );
    }
}
