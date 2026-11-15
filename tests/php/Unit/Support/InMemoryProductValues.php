<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Support;

use Amiut\ProductSpecs\ProductValues\ProductValueDocument;
use Amiut\ProductSpecs\ProductValues\ProductValueRepository;

final class InMemoryProductValues implements ProductValueRepository
{
    /** @var array<int, array<string, mixed>> */
    public array $maps = [];

    /** @var array<int, string> */
    public array $sets = [];

    public int $failOnPostId = 0;

    /** @var list<array{string, int, int}> */
    public array $mentionQueries = [];

    public function read(int $postId): ProductValueDocument
    {
        return ProductValueDocument::fromValues($this->maps[$postId] ?? []);
    }

    public function replace(int $postId, ProductValueDocument $document): bool
    {
        if ($postId === $this->failOnPostId) {
            return false;
        }

        if ($document->values() === []) {
            unset($this->maps[$postId]);

            return true;
        }

        $this->maps[$postId] = $document->values();

        return true;
    }

    public function delete(int $postId): bool
    {
        unset($this->maps[$postId]);

        return true;
    }

    public function assignedSet(int $postId): ?string
    {
        return $this->sets[$postId] ?? null;
    }

    public function assignSet(int $postId, ?string $setSlug): bool
    {
        if ($setSlug === null) {
            unset($this->sets[$postId]);
        } else {
            $this->sets[$postId] = $setSlug;
        }

        return true;
    }

    public function countUsing(string $specificationKey, array $values): int
    {
        $count = 0;

        foreach ($this->valueMapsWith($specificationKey) as $map) {
            if (in_array($map[$specificationKey], $values, true)) {
                $count++;
            }
        }

        return $count;
    }

    public function valueMapsWith(string $specificationKey): array
    {
        return array_values(array_filter(
            $this->maps,
            static fn (array $map): bool => array_key_exists($specificationKey, $map)
        ));
    }

    public function countHolding(string $specificationKey): int
    {
        return count($this->valueMapsWith($specificationKey));
    }

    // A map that carries the key as a value mentions it too, as the stored form does.
    public function postIdsMentioning(string $specificationKey, int $afterPostId, int $limit): array
    {
        $this->mentionQueries[] = [$specificationKey, $afterPostId, $limit];
        ksort($this->maps);
        $postIds = [];

        foreach ($this->maps as $postId => $map) {
            if ($postId <= $afterPostId) {
                continue;
            }

            if (array_key_exists($specificationKey, $map) || in_array($specificationKey, $map, true)) {
                $postIds[] = $postId;
            }

            if (count($postIds) === $limit) {
                break;
            }
        }

        return $postIds;
    }
}
