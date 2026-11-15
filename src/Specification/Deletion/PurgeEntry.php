<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Deletion;

final class PurgeEntry
{
    private function __construct(
        private readonly string $slug,
        private readonly int $cursor,
        private readonly int $queuedAt,
        private readonly ?string $failure
    ) {
    }

    public static function queued(string $slug, int $cursor, int $queuedAt): self
    {
        return new self($slug, $cursor, $queuedAt, null);
    }

    public static function fromStorage(mixed $stored): ?self
    {
        if (!is_array($stored) || !is_string($stored['slug'] ?? null) || $stored['slug'] === '') {
            return null;
        }

        $failure = $stored['failure'] ?? null;

        return new self(
            $stored['slug'],
            is_int($stored['cursor'] ?? null) ? $stored['cursor'] : 0,
            is_int($stored['queued_at'] ?? null) ? $stored['queued_at'] : 0,
            is_string($failure) ? $failure : null
        );
    }

    public function slug(): string
    {
        return $this->slug;
    }

    /**
     * The id of the last product examined.
     */
    public function cursor(): int
    {
        return $this->cursor;
    }

    public function queuedAt(): int
    {
        return $this->queuedAt;
    }

    public function failure(): ?string
    {
        return $this->failure;
    }

    public function isPending(): bool
    {
        return $this->failure === null;
    }

    public function at(int $cursor): self
    {
        return new self($this->slug, $cursor, $this->queuedAt, null);
    }

    public function failed(string $message): self
    {
        return new self($this->slug, $this->cursor, $this->queuedAt, $message);
    }

    public function retried(): self
    {
        return new self($this->slug, $this->cursor, $this->queuedAt, null);
    }

    /**
     * @return array{slug: string, cursor: int, queued_at: int, failure: ?string}
     */
    public function toStorage(): array
    {
        return [
            'slug' => $this->slug,
            'cursor' => $this->cursor,
            'queued_at' => $this->queuedAt,
            'failure' => $this->failure,
        ];
    }
}
