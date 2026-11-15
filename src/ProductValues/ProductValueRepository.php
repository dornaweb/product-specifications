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

    /**
     * @param list<string> $values
     */
    public function countUsing(string $specificationKey, array $values): int;

    /**
     * The value maps of every product and variation outside the trash that hold a value
     * for the specification.
     *
     * @return list<array<string, mixed>>
     */
    public function valueMapsWith(string $specificationKey): array;

    /**
     * How many products and variations outside the trash hold a value for the specification.
     */
    public function countHolding(string $specificationKey): int;

    /**
     * The products and variations whose stored map mentions the key, trashed ones
     * included, in id order after the given id. A map can mention a key as a value rather
     * than hold it, so a reader confirms against the decoded document.
     *
     * @return list<int>
     */
    public function postIdsMentioning(string $specificationKey, int $afterPostId, int $limit): array;
}
