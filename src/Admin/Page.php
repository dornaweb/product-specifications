<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Admin;

final class Page
{
    public function __construct(
        private readonly string $id,
        private readonly string $title,
        private readonly string $path,
        private readonly string $parent,
        private readonly string $capability,
        private readonly int $menuOrder
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function parent(): string
    {
        return $this->parent;
    }

    public function capability(): string
    {
        return $this->capability;
    }

    public function menuOrder(): int
    {
        return $this->menuOrder;
    }
}
