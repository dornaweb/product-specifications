<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\ImportExport;

use Amiut\ProductSpecs\SpecificationSet\SpecificationSet;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetRepository;

final class InMemorySetRepository implements SpecificationSetRepository
{
    /**
     * @param array<string, SpecificationSet> $bySlug
     */
    public function __construct(private readonly array $bySlug = [])
    {
    }

    public function byId(int $id): ?SpecificationSet
    {
        return null;
    }

    public function bySlug(string $slug): ?SpecificationSet
    {
        return $this->bySlug[$slug] ?? null;
    }

    public function all(array $args = []): array
    {
        return array_values($this->bySlug);
    }

    public function save(SpecificationSet $set): int
    {
        return 1;
    }

    public function delete(int $id): bool
    {
        return true;
    }
}
