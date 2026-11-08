<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet;

final class SpecificationSetItem
{
    private function __construct(
        private readonly int $termId,
        private readonly string $slug,
        private readonly bool $visible
    ) {
    }

    public static function create(
        int $termId,
        string $slug,
        bool $visible = true
    ): self {
        return new self($termId, $slug, $visible);
    }

    public function termId(): int
    {
        return $this->termId;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function visible(): bool
    {
        return $this->visible;
    }
}
