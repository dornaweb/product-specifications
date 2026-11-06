<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Migration;

use Amiut\ProductSpecs\Migration\ChunkResult;
use Amiut\ProductSpecs\Migration\MigrationStep;
use RuntimeException;

final class FakeStep implements MigrationStep
{
    /** @var list<int> */
    public array $cursors = [];
    public bool $throwOnNextRun = false;

    /**
     * @param list<int> $ids
     * @param list<int> $skip
     * @param list<int> $fail
     */
    public function __construct(
        private readonly string $key,
        private readonly array $ids,
        private readonly array $skip,
        private readonly array $fail
    ) {
    }

    public function key(): string
    {
        return $this->key;
    }

    public function total(): int
    {
        return count($this->ids);
    }

    public function run(int $cursor, int $limit): ChunkResult
    {
        $this->cursors[] = $cursor;

        if ($this->throwOnNextRun) {
            $this->throwOnNextRun = false;
            throw new RuntimeException('database went away');
        }

        $remaining = array_values(array_filter($this->ids, static fn (int $id): bool => $id > $cursor));
        $batch = array_slice($remaining, 0, $limit);
        $last = $batch === [] ? $cursor : $batch[count($batch) - 1];

        return new ChunkResult(
            count($remaining) > count($batch) ? $last : null,
            count($batch),
            array_values(array_intersect($batch, $this->skip)),
            array_fill_keys(array_values(array_intersect($batch, $this->fail)), 'broken')
        );
    }
}
