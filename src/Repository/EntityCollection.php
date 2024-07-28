<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Repository;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * @template Entity
 * @implements IteratorAggregate<int, Entity>
 */
class EntityCollection implements IteratorAggregate, Countable
{
    /** @var array<Entity> */
    private array $entities = [];
    private int $total;
    private int $perPage;
    private int $currentPage;

    /**
     * @param array<Entity> $entities
     */
    public function __construct(array $entities, int $total, int $perPage, int $currentPage)
    {
        $this->entities = $entities;
        $this->total = $total;
        $this->perPage = $perPage;
        $this->currentPage = $currentPage;
    }

    public static function empty(): self
    {
        return new self([], 0, 0, 1);
    }

    /**
     * @return ArrayIterator<int, Entity>
     */
    public function getIterator(): ArrayIterator
    {
        return new \ArrayIterator($this->entities);
    }

    public function count(): int
    {
        return count($this->entities);
    }

    public function entities(): array
    {
        return $this->entities;
    }

    public function total(): int
    {
        return $this->total;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function currentPage(): int
    {
        return $this->currentPage;
    }

    public function totalPages(): int
    {
        return (int) ceil($this->total / $this->perPage());
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage() < $this->totalPages();
    }

    public function hasPreviousPage(): bool
    {
        return $this->currentPage() > 1;
    }
}
