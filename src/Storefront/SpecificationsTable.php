<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Storefront;

final class SpecificationsTable
{
    /**
     * @param list<TableGroup> $groups
     */
    public function __construct(private readonly array $groups)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<TableGroup>
     */
    public function groups(): array
    {
        return $this->groups;
    }

    public function isEmpty(): bool
    {
        return $this->groups === [];
    }
}
