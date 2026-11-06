<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification;

final class SpecificationSlug
{
    private const SUFFIX_BYTES = 4;

    private function __construct(private readonly string $value)
    {
    }

    public static function forName(string $name): self
    {
        $stem = sanitize_title($name);

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

    private static function isCanonicalInteger(string $slug): bool
    {
        return (string) (int) $slug === $slug;
    }
}
