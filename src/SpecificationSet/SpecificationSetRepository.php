<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet;

interface SpecificationSetRepository
{
    public function byId(int $id): ?SpecificationSet;

    public function bySlug(string $slug): ?SpecificationSet;

    /**
     * @param array<string, mixed> $args
     * @return array<SpecificationSet>
     */
    public function all(array $args = []): array;

    public function save(SpecificationSet $set): int;

    public function delete(int $id): bool;
}
