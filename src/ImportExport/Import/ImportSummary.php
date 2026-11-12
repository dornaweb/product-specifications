<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport\Import;

use Amiut\ProductSpecs\ImportExport\EntryOutcome;
use Amiut\ProductSpecs\ImportExport\ImportPlan;

/**
 * Counts per section and status, plus the entries a merchant needs to read: conflicts,
 * skips, failures and anything with notes. The list of those entries is capped, so the
 * summary stays small however large the file is.
 */
final class ImportSummary
{
    public const NOTABLE_LIMIT = 200;

    private const ROUTINE = [EntryOutcome::CREATE, EntryOutcome::UPDATE, EntryOutcome::UNCHANGED];

    /**
     * @param array<string, array<string, int>> $counts
     * @param list<array{section: string, status: string, label: string, notes: list<string>}> $notable
     */
    private function __construct(
        private readonly array $counts,
        private readonly array $notable,
        private readonly int $notableTotal
    ) {
    }

    public static function empty(): self
    {
        return new self([], [], 0);
    }

    public static function ofPlan(ImportPlan $plan): self
    {
        $summary = self::empty();

        foreach ($plan->sectionKeys() as $sectionKey) {
            foreach ($plan->outcomes($sectionKey) as $outcome) {
                $summary = $summary->with($sectionKey, $outcome);
            }
        }

        return $summary;
    }

    public static function fromStorage(mixed $stored): self
    {
        if (!is_array($stored)) {
            return self::empty();
        }

        /** @var array<string, array<string, int>> $counts */
        $counts = is_array($stored['counts'] ?? null) ? $stored['counts'] : [];
        /** @var list<array{section: string, status: string, label: string, notes: list<string>}> $notable */
        $notable = is_array($stored['notable'] ?? null) ? $stored['notable'] : [];

        return new self(
            $counts,
            $notable,
            is_int($stored['notable_total'] ?? null) ? $stored['notable_total'] : count($notable)
        );
    }

    public function with(string $sectionKey, EntryOutcome $outcome): self
    {
        $counts = $this->counts;
        $counts[$sectionKey][$outcome->status()] = ($counts[$sectionKey][$outcome->status()] ?? 0) + 1;

        if ($outcome->notes() === [] && in_array($outcome->status(), self::ROUTINE, true)) {
            return new self($counts, $this->notable, $this->notableTotal);
        }

        $notable = $this->notable;

        if (count($notable) < self::NOTABLE_LIMIT) {
            $notable[] = ['section' => $sectionKey, ...$outcome->toArray()];
        }

        return new self($counts, $notable, $this->notableTotal + 1);
    }

    /**
     * @return array<string, array<string, int>>
     */
    public function counts(): array
    {
        return $this->counts;
    }

    /**
     * @return array{
     *     counts: array<string, array<string, int>>,
     *     notable: list<array{section: string, status: string, label: string, notes: list<string>}>,
     *     notable_total: int
     * }
     */
    public function toArray(): array
    {
        return ['counts' => $this->counts, 'notable' => $this->notable, 'notable_total' => $this->notableTotal];
    }
}
