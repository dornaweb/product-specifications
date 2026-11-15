<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport;

/**
 * Finds the object a value entry belongs to on this store. One matcher per object type,
 * so values can follow other post types later without changing the values section.
 */
interface ObjectMatcher
{
    public function type(): string;

    /**
     * @param array<string, mixed> $object The entry's "object" member.
     */
    public function find(array $object): ?int;

    /**
     * @return array<string, mixed> What identifies the object in a file, "type" included.
     */
    public function identify(int $objectId): array;

    /**
     * @param array<string, mixed> $object
     */
    public function describe(array $object): string;
}
