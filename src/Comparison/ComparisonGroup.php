<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Comparison;

final class ComparisonGroup
{
    /**
     * @param list<ComparisonRow> $rows
     */
    public function __construct(
        private readonly string $label,
        private readonly array $rows
    ) {
    }

    public function label(): string
    {
        return $this->label;
    }

    /**
     * @return list<ComparisonRow>
     */
    public function rows(): array
    {
        return $this->rows;
    }
}
