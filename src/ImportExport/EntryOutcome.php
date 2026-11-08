<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport;

final class EntryOutcome
{
    public const CREATE = 'create';
    public const UPDATE = 'update';
    public const UNCHANGED = 'unchanged';
    public const CONFLICT = 'conflict';
    public const SKIP = 'skip';
    public const FAILED = 'failed';

    /**
     * @param list<string> $notes
     */
    private function __construct(
        private readonly string $status,
        private readonly string $label,
        private readonly array $notes
    ) {
    }

    /**
     * @param list<string> $notes
     */
    public static function create(string $label, array $notes = []): self
    {
        return new self(self::CREATE, $label, $notes);
    }

    /**
     * @param list<string> $notes
     */
    public static function update(string $label, array $notes = []): self
    {
        return new self(self::UPDATE, $label, $notes);
    }

    /**
     * @param list<string> $notes
     */
    public static function unchanged(string $label, array $notes = []): self
    {
        return new self(self::UNCHANGED, $label, $notes);
    }

    public static function conflict(string $label, string $reason): self
    {
        return new self(self::CONFLICT, $label, [$reason]);
    }

    public static function skip(string $label, string ...$reasons): self
    {
        return new self(self::SKIP, $label, array_values($reasons));
    }

    public static function failed(string $label, string $reason): self
    {
        return new self(self::FAILED, $label, [$reason]);
    }

    /**
     * @param array<string, mixed> $stored
     */
    public static function fromArray(array $stored): self
    {
        $notes = is_array($stored['notes'] ?? null) ? $stored['notes'] : [];

        return new self(
            is_string($stored['status'] ?? null) ? $stored['status'] : self::SKIP,
            is_string($stored['label'] ?? null) ? $stored['label'] : '',
            array_values(array_filter($notes, 'is_string'))
        );
    }

    public function status(): string
    {
        return $this->status;
    }

    public function label(): string
    {
        return $this->label;
    }

    /**
     * @return list<string>
     */
    public function notes(): array
    {
        return $this->notes;
    }

    public function writes(): bool
    {
        return $this->status === self::CREATE || $this->status === self::UPDATE;
    }

    public function keepsItem(): bool
    {
        return $this->writes() || $this->status === self::UNCHANGED;
    }

    /**
     * @return array{status: string, label: string, notes: list<string>}
     */
    public function toArray(): array
    {
        return ['status' => $this->status, 'label' => $this->label, 'notes' => $this->notes];
    }
}
