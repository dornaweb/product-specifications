<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Deletion;

final class PurgeProgress
{
    private function __construct(
        private readonly int $cursor,
        private readonly bool $finished,
        private readonly ?string $failure
    ) {
    }

    public static function finished(): self
    {
        return new self(0, true, null);
    }

    public static function continuing(int $cursor): self
    {
        return new self($cursor, false, null);
    }

    public static function failed(int $cursor, string $message): self
    {
        return new self($cursor, false, $message);
    }

    /**
     * The id of the last product whose value was removed; the next run continues after it.
     */
    public function cursor(): int
    {
        return $this->cursor;
    }

    public function isFinished(): bool
    {
        return $this->finished;
    }

    public function failure(): ?string
    {
        return $this->failure;
    }
}
