<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport;

interface Section
{
    public function key(): string;

    /**
     * @return list<string> The keys of the sections this one reads, which run first.
     */
    public function dependsOn(): array;

    /**
     * @param list<mixed> $entries
     * @return array<int, list<string>> The problems of each entry that cannot be imported, keyed
     *                                  by its index. Each starts with the entry's path inside the
     *                                  section, such as "[2].name: is required".
     */
    public function validate(array $entries): array;

    /**
     * @return iterable<array<string, mixed>>
     */
    public function export(): iterable;

    /**
     * Decides what importing the entry would do, without writing anything.
     *
     * @param array<string, mixed> $entry
     */
    public function planEntry(array $entry, ImportPlan $plan): EntryOutcome;

    /**
     * @param array<string, mixed> $entry
     */
    public function importEntry(array $entry, ImportPlan $plan): EntryOutcome;
}
