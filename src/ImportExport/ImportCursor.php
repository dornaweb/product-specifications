<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport;

final class ImportCursor
{
    public function __construct(
        private readonly int $section,
        private readonly int $entry
    ) {
    }

    public static function start(): self
    {
        return new self(0, 0);
    }

    /**
     * @param array<string, mixed> $stored
     */
    public static function fromArray(array $stored): self
    {
        return new self(
            is_int($stored['section'] ?? null) ? $stored['section'] : 0,
            is_int($stored['entry'] ?? null) ? $stored['entry'] : 0
        );
    }

    public function section(): int
    {
        return $this->section;
    }

    public function entry(): int
    {
        return $this->entry;
    }

    /**
     * @return array{section: int, entry: int}
     */
    public function toArray(): array
    {
        return ['section' => $this->section, 'entry' => $this->entry];
    }
}
