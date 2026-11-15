<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration;

final class ChunkResult
{
    /**
     * @param list<int> $skipped
     * @param array<int, string> $errors Messages keyed by item id.
     */
    public function __construct(
        private readonly ?int $nextCursor,
        private readonly int $processed,
        private readonly array $skipped = [],
        private readonly array $errors = []
    ) {
    }

    public function nextCursor(): ?int
    {
        return $this->nextCursor;
    }

    public function isFinished(): bool
    {
        return $this->nextCursor === null;
    }

    public function processed(): int
    {
        return $this->processed;
    }

    /**
     * @return list<int>
     */
    public function skipped(): array
    {
        return $this->skipped;
    }

    /**
     * @return array<int, string>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
