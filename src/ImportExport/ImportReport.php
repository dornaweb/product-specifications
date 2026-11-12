<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport;

final class ImportReport
{
    /**
     * @var array<string, array<int, EntryOutcome>>
     */
    private array $outcomes = [];

    public function record(string $sectionKey, int $index, EntryOutcome $outcome): void
    {
        $this->outcomes[$sectionKey][$index] = $outcome;
    }

    /**
     * @return array<int, EntryOutcome>
     */
    public function outcomes(string $sectionKey): array
    {
        return $this->outcomes[$sectionKey] ?? [];
    }
}
