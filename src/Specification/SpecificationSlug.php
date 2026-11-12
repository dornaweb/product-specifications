<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification;

final class SpecificationSlug
{
    private const SUFFIX_BYTES = 4;
    private const COLUMN_LENGTH = 200;

    private function __construct(private readonly string $value)
    {
    }

    public static function forName(string $name): self
    {
        $stem = self::stemFor($name);

        if ($stem === '') {
            $stem = 'specification';
        }

        return new self($stem . '-' . bin2hex(random_bytes(self::SUFFIX_BYTES)));
    }

    public static function preserved(string $slug): self
    {
        if (self::isCanonicalInteger($slug)) {
            return new self($slug . '-' . bin2hex(random_bytes(self::SUFFIX_BYTES)));
        }

        return new self($slug);
    }

    public function value(): string
    {
        return $this->value;
    }

    // Non-Latin characters are stored percent-encoded, up to nine characters per
    // character, so the name is shortened until its encoded form leaves room
    // for the suffix inside the slug column.
    private static function stemFor(string $name): string
    {
        $limit = self::COLUMN_LENGTH - 1 - self::SUFFIX_BYTES * 2;
        $name = mb_substr($name, 0, $limit);
        $stem = sanitize_title($name);

        while (strlen($stem) > $limit) {
            $name = mb_substr($name, 0, -1);
            $stem = sanitize_title($name);
        }

        return $stem;
    }

    private static function isCanonicalInteger(string $slug): bool
    {
        return (string) (int) $slug === $slug;
    }
}
