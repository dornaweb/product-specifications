<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ProductValues;

interface ProductValueRepository
{
    public function read(int $postId): ProductValueDocument;

    public function replace(int $postId, ProductValueDocument $document): bool;

    public function delete(int $postId): bool;

    public function assignedSet(int $postId): ?string;

    public function assignSet(int $postId, ?string $setSlug): bool;

    public function countContaining(string $specificationKey): int;

    public function purge(string $specificationKey): bool;
}
