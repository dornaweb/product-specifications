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

    /**
     * Options are stored either as a bare list of choices or as a map keyed by
     * the owning type's option names. Both are read; neither is rewritten.
     */
    public static function fromStorage(mixed $stored): self
    {
        if (!is_array($stored)) {
            return new self([]);
        }

        if ($stored === []) {
            return new self([]);
        }

        if (self::isChoiceList($stored)) {
            return new self([self::CHOICES_KEY => self::stringList($stored)]);
        }

        /** @var array<string, mixed> $stored */
        return self::fromMap($stored);
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
     * A choice list is integer-keyed but not necessarily gapless.
     *
     * @param array<array-key, mixed> $stored
     */
    private static function isChoiceList(array $stored): bool
    {
        foreach (array_keys($stored) as $key) {
            if (!is_int($key)) {
                return false;
            }
        }

        return true;
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
