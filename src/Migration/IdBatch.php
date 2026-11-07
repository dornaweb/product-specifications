<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration;

final class IdBatch
{
    /**
     * @param list<int> $ids
     */
    private function __construct(
        private readonly array $ids,
        private readonly bool $hasMore
    ) {
    }

    /**
     * @param array<int> $ids
     */
    public static function after(array $ids, int $cursor, int $limit): self
    {
        $remaining = array_values(array_filter($ids, static fn (int $id): bool => $id > $cursor));
        sort($remaining);
        $batch = array_slice($remaining, 0, max(1, $limit));

        return new self($batch, count($remaining) > count($batch));
    }

    /**
     * @return list<int>
     */
    public function ids(): array
    {
        return $this->ids;
    }

    /**
     * @param list<int> $skipped
     * @param array<int, string> $errors
     */
    public function result(int $processed, array $skipped = [], array $errors = []): ChunkResult
    {
        $last = $this->ids === [] ? null : $this->ids[count($this->ids) - 1];

        return new ChunkResult($this->hasMore ? $last : null, $processed, $skipped, $errors);
    }
}
