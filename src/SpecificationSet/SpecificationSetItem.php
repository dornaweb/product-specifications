<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet;

final class SpecificationSetItem
{
    private function __construct(
        private readonly string $slug,
        private readonly bool $visible
    ) {
    }

    public static function create(string $slug, bool $visible = true): self
    {
        return new self($slug, $visible);
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
