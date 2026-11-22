<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Comparison;

final class ComparisonRow
{
    /**
     * @param list<?string> $cells
     */
    public function __construct(
        private readonly string $name,
        private readonly array $cells
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return list<?string>
     */
    public function cells(): array
    {
        return $this->cells;
    }
}
