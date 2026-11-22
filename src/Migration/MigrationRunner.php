<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration;

use Amiut\ProductSpecs\ProductValues\ProductValueWriter;
use Amiut\ProductSpecs\Upgrade\DataVersion;
use Amiut\ProductSpecs\Upgrade\DataVersionRepository;
use RuntimeException;
use Throwable;

final class MigrationRunner
{
    public const CHUNK_SIZE = 100;

    /**
     * @param list<MigrationStep> $steps In the order they run.
     */
    public function __construct(
        private readonly MigrationRunRepository $runs,
        private readonly DataVersionRepository $versions,
        private readonly LegacyGroupTaxonomy $groupTaxonomy,
        private readonly array $steps,
        private readonly int $chunkSize = self::CHUNK_SIZE
    ) {
    }

    public function run(): MigrationRun
    {
        return $this->runs->stored();
    }

    public function start(): MigrationRun
    {
        $run = $this->runs->stored();
        $firstStep = $this->steps[0] ?? null;

        if (
            $firstStep === null
            || $run->isRunning()
            || $run->isFailed()
            || $this->versions->stored()->isMigrated()
        ) {
            return $run;
        }

        $started = MigrationRun::started($firstStep->key(), $this->total(), time());
        $this->runs->store($started);

        return $started;
    }

    public function completeIfNothingToMigrate(): void
    {
        $run = $this->runs->stored();

        if ($run->isRunning() || $run->isFailed() || $this->versions->stored()->isMigrated()) {
            return;
        }

        try {
            $totals = $this->totals();
        } catch (Throwable) {
            return;
        }

        if (array_sum($totals) > 0) {
            return;
        }

        $this->versions->store(DataVersion::migrated());
    }

    public function reopen(): void
    {
        $run = $this->runs->stored();

        if ($run->isRunning() || $run->isFailed() || !$this->versions->stored()->isMigrated()) {
            return;
        }

        if (array_sum($this->totals()) > 0) {
            $this->versions->store(DataVersion::legacyInstall());
        }
    }

    public function runChunk(): MigrationRun
    {
        $run = $this->runs->stored();

        if (!$run->isRunning()) {
            return $run;
        }

        $this->groupTaxonomy->register();

        try {
            $index = $this->indexOf($run->step());
            $result = $this->steps[$index]->run($run->cursor(), $this->chunkSize);
            $run = $run->withChunk($result);

            if ($result->isFinished()) {
                $run = $this->advance($run, $index);
            }
        } catch (Throwable $exception) {
            $run = $run->failed($exception->getMessage());
        }

        $this->runs->store($run);

        if ($run->isComplete()) {
            do_action(ProductValueWriter::REBUILD_ACTION);
        }

        return $run;
    }

    public function resume(): MigrationRun
    {
        $run = $this->runs->stored();

        if (!$run->isFailed()) {
            return $run;
        }

        $resumed = $run->resumed();
        $this->runs->store($resumed);

        return $resumed;
    }

    /**
     * @return list<array{key: string, total: int}>
     */
    public function scope(): array
    {
        return array_map(
            static fn (MigrationStep $step): array => ['key' => $step->key(), 'total' => $step->total()],
            $this->steps
        );
    }

    private function advance(MigrationRun $run, int $index): MigrationRun
    {
        $next = $this->steps[$index + 1] ?? null;

        if ($next !== null) {
            return $run->atStep($next->key());
        }

        $this->versions->store(DataVersion::migrated());

        return $run->completed();
    }

    private function indexOf(string $key): int
    {
        foreach ($this->steps as $index => $step) {
            if ($step->key() === $key) {
                return $index;
            }
        }

        throw new RuntimeException(
            sprintf(
                /* translators: %s: the key of a migration step. */
                __('The migration step "%s" is not available.', 'product-specifications'),
                $key
            )
        );
    }

    private function total(): int
    {
        return array_sum($this->totals());
    }

    /**
     * @return list<int>
     */
    private function totals(): array
    {
        return array_map(static fn (MigrationStep $step): int => $step->total(), $this->steps);
    }
}
