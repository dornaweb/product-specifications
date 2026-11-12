<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration;

interface MigrationStep
{
    public function key(): string;

    public function total(): int;

    /**
     * Processes up to $limit items whose id is above $cursor. An item that already
     * holds data in the current shape is skipped, never overwritten.
     */
    public function run(int $cursor, int $limit): ChunkResult;
}
