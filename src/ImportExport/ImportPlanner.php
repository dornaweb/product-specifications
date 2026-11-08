<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport;

final class ImportPlanner
{
    public function __construct(private readonly SectionRegistry $sections)
    {
    }

    public function plan(Package $package): ImportPlan
    {
        $plan = new ImportPlan();

        foreach ($package->sectionKeys() as $key) {
            if (!$this->sections->has($key)) {
                $plan->recordUnknownSection($key);
            }
        }

        foreach ($this->sections->ordered() as $section) {
            foreach ($package->entries($section->key()) as $index => $entry) {
                $problems = $package->problems($section->key(), $index);
                $outcome = $problems === []
                    ? $section->planEntry($entry, $plan)
                    : EntryOutcome::skip($this->label($entry, $index), ...$problems);

                $plan->record($section->key(), $index, $outcome);
            }
        }

        return $plan;
    }

    /**
     * @param array<string, mixed> $entry
     */
    private function label(array $entry, int $index): string
    {
        $slug = $entry['slug'] ?? null;

        return is_string($slug) && $slug !== '' ? $slug : "[{$index}]";
    }
}
