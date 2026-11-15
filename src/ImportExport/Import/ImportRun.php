<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport\Import;

use Amiut\ProductSpecs\ImportExport\EntryOutcome;

/**
 * One import, from the moment its file is planned until the merchant dismisses its
 * report. Immutable: every change returns a new run.
 *
 * The run is saved after every entry, so it holds only what stays small. The plan and the
 * full report are kept in files beside the uploaded package.
 */
final class ImportRun
{
    public const PLANNED = 'planned';
    public const RUNNING = 'running';
    public const FAILED = 'failed';
    public const COMPLETE = 'complete';

    private const STATUSES = [self::PLANNED, self::RUNNING, self::FAILED, self::COMPLETE];

    /**
     * @param list<string> $unknownSections
     * @param array{section: int, entry: int}|null $cursor
     */
    private function __construct(
        private readonly string $status,
        private readonly string $fileName,
        private readonly string $storedFile,
        private readonly array $unknownSections,
        private readonly ImportSummary $summary,
        private readonly ?array $cursor,
        private readonly int $processed,
        private readonly int $total,
        private readonly ?string $failure
    ) {
    }

    /**
     * @param list<string> $unknownSections
     */
    public static function planned(
        string $fileName,
        string $storedFile,
        array $unknownSections,
        ImportSummary $planned,
        int $total
    ): self {
        return new self(self::PLANNED, $fileName, $storedFile, $unknownSections, $planned, null, 0, $total, null);
    }

    public static function fromStorage(mixed $stored): ?self
    {
        if (!is_array($stored) || !in_array($stored['status'] ?? null, self::STATUSES, true)) {
            return null;
        }

        $cursor = is_array($stored['cursor'] ?? null) ? $stored['cursor'] : null;
        $unknown = is_array($stored['unknown_sections'] ?? null) ? $stored['unknown_sections'] : [];

        return new self(
            (string) $stored['status'],
            is_string($stored['file_name'] ?? null) ? $stored['file_name'] : '',
            is_string($stored['stored_file'] ?? null) ? $stored['stored_file'] : '',
            array_values(array_filter($unknown, 'is_string')),
            ImportSummary::fromStorage($stored['summary'] ?? null),
            $cursor === null ? null : [
                'section' => is_int($cursor['section'] ?? null) ? $cursor['section'] : 0,
                'entry' => is_int($cursor['entry'] ?? null) ? $cursor['entry'] : 0,
            ],
            is_int($stored['processed'] ?? null) ? $stored['processed'] : 0,
            is_int($stored['total'] ?? null) ? $stored['total'] : 0,
            is_string($stored['failure'] ?? null) ? $stored['failure'] : null
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toStorage(): array
    {
        return [
            'status' => $this->status,
            'file_name' => $this->fileName,
            'stored_file' => $this->storedFile,
            'unknown_sections' => $this->unknownSections,
            'summary' => $this->summary->toArray(),
            'cursor' => $this->cursor,
            'processed' => $this->processed,
            'total' => $this->total,
            'failure' => $this->failure,
        ];
    }

    /**
     * A planned run's summary describes the plan; once started, it describes what was imported.
     */
    public function started(): self
    {
        $summary = $this->status === self::PLANNED ? ImportSummary::empty() : $this->summary;

        return $this->with(self::RUNNING, $this->cursor ?? ['section' => 0, 'entry' => 0], $summary, $this->processed, null);
    }

    public function failed(string $failure): self
    {
        return $this->with(self::FAILED, $this->cursor, $this->summary, $this->processed, $failure);
    }

    public function completed(): self
    {
        return $this->with(self::COMPLETE, null, $this->summary, $this->processed, null);
    }

    /**
     * @param array{section: int, entry: int} $cursor
     */
    public function recorded(string $sectionKey, EntryOutcome $outcome, array $cursor): self
    {
        return $this->with(
            $this->status,
            $cursor,
            $this->summary->with($sectionKey, $outcome),
            $this->processed + 1,
            $this->failure
        );
    }

    /**
     * @param array{section: int, entry: int} $cursor
     */
    public function at(array $cursor): self
    {
        return $this->with($this->status, $cursor, $this->summary, $this->processed, $this->failure);
    }

    public function status(): string
    {
        return $this->status;
    }

    public function isRunning(): bool
    {
        return $this->status === self::RUNNING;
    }

    public function isComplete(): bool
    {
        return $this->status === self::COMPLETE;
    }

    public function fileName(): string
    {
        return $this->fileName;
    }

    public function storedFile(): string
    {
        return $this->storedFile;
    }

    public function planFile(): string
    {
        return $this->storedFile . '.plan';
    }

    public function reportFile(): string
    {
        return $this->storedFile . '.report';
    }

    /**
     * @return list<string>
     */
    public function unknownSections(): array
    {
        return $this->unknownSections;
    }

    public function summary(): ImportSummary
    {
        return $this->summary;
    }

    /**
     * @return array{section: int, entry: int}|null
     */
    public function cursor(): ?array
    {
        return $this->cursor;
    }

    public function processed(): int
    {
        return $this->processed;
    }

    public function total(): int
    {
        return $this->total;
    }

    public function failure(): ?string
    {
        return $this->failure;
    }

    /**
     * @param array{section: int, entry: int}|null $cursor
     */
    private function with(
        string $status,
        ?array $cursor,
        ImportSummary $summary,
        int $processed,
        ?string $failure
    ): self {
        return new self(
            $status,
            $this->fileName,
            $this->storedFile,
            $this->unknownSections,
            $summary,
            $cursor,
            $processed,
            $this->total,
            $failure
        );
    }
}
