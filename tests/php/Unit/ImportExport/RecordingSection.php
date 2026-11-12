<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\ImportExport;

use Amiut\ProductSpecs\ImportExport\EntryOutcome;
use Amiut\ProductSpecs\ImportExport\ImportPlan;
use Amiut\ProductSpecs\ImportExport\Section;
use RuntimeException;

final class RecordingSection implements Section
{
    public const KEY = 'recording';

    /**
     * @var list<int>
     */
    public array $imported = [];

    public function __construct(public ?int $failOn = null)
    {
    }

    public function key(): string
    {
        return self::KEY;
    }

    public function dependsOn(): array
    {
        return [];
    }

    public function validate(array $entries): array
    {
        return [];
    }

    public function export(): iterable
    {
        return [];
    }

    public function planEntry(array $entry, ImportPlan $plan): EntryOutcome
    {
        return EntryOutcome::create((string) $this->index($entry));
    }

    public function importEntry(array $entry, ImportPlan $plan): EntryOutcome
    {
        $index = $this->index($entry);

        if ($index === $this->failOn) {
            throw new RuntimeException("Entry {$index} broke.");
        }

        $this->imported[] = $index;

        return EntryOutcome::create((string) $index);
    }

    /**
     * @param array<string, mixed> $entry
     */
    private function index(array $entry): int
    {
        return is_int($entry['index'] ?? null) ? $entry['index'] : -1;
    }
}
