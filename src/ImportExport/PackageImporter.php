<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport;

final class PackageImporter
{
    public function __construct(private readonly SectionRegistry $sections)
    {
    }

    public function import(Package $package, ImportPlan $plan): ImportReport
    {
        $report = new ImportReport();
        $this->importFrom(
            $package,
            $plan,
            ImportCursor::start(),
            PHP_INT_MAX,
            static function (string $sectionKey, int $index, EntryOutcome $outcome) use ($report): void {
                $report->record($sectionKey, $index, $outcome);
            }
        );

        return $report;
    }

    /**
     * Writes only what the plan decided to create or update, in dependency order. Every
     * other planned entry carries its planned outcome.
     *
     * @param callable(string, int, EntryOutcome, ImportCursor): void $record Also handed where to
     *        continue after the entry it records.
     * @return ImportCursor|null Where to continue, or null once everything is imported.
     */
    public function importFrom(
        Package $package,
        ImportPlan $plan,
        ImportCursor $cursor,
        int $limit,
        callable $record
    ): ?ImportCursor {
        $sections = $this->sections->ordered();
        $done = 0;

        for ($sectionIndex = $cursor->section(); $sectionIndex < count($sections); $sectionIndex++) {
            $section = $sections[$sectionIndex];
            $entries = $package->entries($section->key());
            $first = $sectionIndex === $cursor->section() ? $cursor->entry() : 0;

            for ($entryIndex = $first; $entryIndex < count($entries); $entryIndex++) {
                $planned = $plan->outcome($section->key(), $entryIndex);

                if ($planned === null) {
                    continue;
                }

                if ($done >= $limit) {
                    return new ImportCursor($sectionIndex, $entryIndex);
                }

                $record(
                    $section->key(),
                    $entryIndex,
                    $planned->writes() ? $section->importEntry($entries[$entryIndex], $plan) : $planned,
                    new ImportCursor($sectionIndex, $entryIndex + 1)
                );
                $done++;
            }
        }

        return null;
    }
}
