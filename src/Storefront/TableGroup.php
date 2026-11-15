<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Storefront;

final class TableGroup
{
    /**
     * @param list<TableRow> $rows
     */
    public function __construct(
        private readonly string $key,
        private readonly string $label,
        private readonly array $rows
    ) {
    }

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->label;
    }

    /**
     * @return list<TableRow>
     */
    public function rows(): array
    {
        return $this->rows;
    }
}
