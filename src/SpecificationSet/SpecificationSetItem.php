<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet;

final class SpecificationSetItem
{
    private function __construct(
        private readonly int $termId,
        private readonly string $slug,
        private readonly bool $visible,
        private readonly bool $comparable
    ) {
    }

    public static function create(
        int $termId,
        string $slug,
        bool $visible = true,
        bool $comparable = false
    ): self {
        return new self($termId, $slug, $visible, $comparable);
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

    public function comparable(): bool
    {
        return $this->comparable;
    }
}
