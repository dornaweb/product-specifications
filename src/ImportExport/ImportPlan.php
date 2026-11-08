<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport;

final class ImportPlan
{
    /**
     * @var array<string, array<int, EntryOutcome>>
     */
    private array $outcomes = [];

    /**
     * @var list<string>
     */
    private array $unknownSections = [];

    /**
     * @param array<string, mixed> $stored
     */
    public static function fromArray(array $stored): self
    {
        $plan = new self();
        $sections = is_array($stored['sections'] ?? null) ? $stored['sections'] : [];

        foreach ($sections as $key => $outcomes) {
            foreach (is_array($outcomes) ? $outcomes : [] as $index => $outcome) {
                if (is_string($key) && is_int($index) && is_array($outcome)) {
                    /** @var array<string, mixed> $outcome */
                    $plan->record($key, $index, EntryOutcome::fromArray($outcome));
                }
            }
        }

        foreach (is_array($stored['unknown_sections'] ?? null) ? $stored['unknown_sections'] : [] as $key) {
            if (is_string($key)) {
                $plan->recordUnknownSection($key);
            }
        }

        return $plan;
    }

    public function record(string $sectionKey, int $index, EntryOutcome $outcome): void
    {
        $this->outcomes[$sectionKey][$index] = $outcome;
    }

    public function recordUnknownSection(string $sectionKey): void
    {
        $this->unknownSections[] = $sectionKey;
    }

    public function outcome(string $sectionKey, int $index): ?EntryOutcome
    {
        return $this->outcomes[$sectionKey][$index] ?? null;
    }

    public function outcomeFor(string $sectionKey, string $label): ?EntryOutcome
    {
        foreach ($this->outcomes[$sectionKey] ?? [] as $outcome) {
            if ($outcome->label() === $label) {
                return $outcome;
            }
        }

        return null;
    }

    /**
     * @return array<int, EntryOutcome>
     */
    public function outcomes(string $sectionKey): array
    {
        return $this->outcomes[$sectionKey] ?? [];
    }

    /**
     * @return list<string>
     */
    public function sectionKeys(): array
    {
        return array_keys($this->outcomes);
    }

    public function count(): int
    {
        return array_sum(array_map('count', $this->outcomes));
    }

    /**
     * @return list<string>
     */
    public function unknownSections(): array
    {
        return $this->unknownSections;
    }

    /**
     * @return array{
     *     sections: array<string, array<int, array{status: string, label: string, notes: list<string>}>>,
     *     unknown_sections: list<string>
     * }
     */
    public function toArray(): array
    {
        return [
            'sections' => array_map(
                static fn (array $outcomes): array => array_map(
                    static fn (EntryOutcome $outcome): array => $outcome->toArray(),
                    $outcomes
                ),
                $this->outcomes
            ),
            'unknown_sections' => $this->unknownSections,
        ];
    }
}
