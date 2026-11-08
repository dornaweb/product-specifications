<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport\Import;

use Amiut\ProductSpecs\ImportExport\EntryOutcome;
use Amiut\ProductSpecs\ImportExport\ImportCursor;
use Amiut\ProductSpecs\ImportExport\ImportPlan;
use Amiut\ProductSpecs\ImportExport\ImportPlanner;
use Amiut\ProductSpecs\ImportExport\InvalidPackage;
use Amiut\ProductSpecs\ImportExport\MigrationRequired;
use Amiut\ProductSpecs\ImportExport\Package;
use Amiut\ProductSpecs\ImportExport\PackageImporter;
use Amiut\ProductSpecs\ImportExport\PackageReader;
use Amiut\ProductSpecs\Upgrade\MigrationState;
use RuntimeException;
use Throwable;

final class ImportRunner
{
    public const CHUNK_SIZE = 25;

    /**
     * @var array{string, Package, ImportPlan}|null
     */
    private ?array $loaded = null;

    public function __construct(
        private readonly ImportRunRepository $runs,
        private readonly ImportStorage $storage,
        private readonly PackageReader $reader,
        private readonly ImportPlanner $planner,
        private readonly PackageImporter $importer,
        private readonly MigrationState $migration
    ) {
    }

    public function run(): ?ImportRun
    {
        return $this->runs->stored();
    }

    public function isRunning(): bool
    {
        return $this->runs->stored()?->isRunning() ?? false;
    }

    /**
     * Validates and plans a file, replacing any run that is not in progress.
     *
     * @throws InvalidPackage
     * @throws RuntimeException While another import is running, or before the migration is complete.
     */
    public function prepare(string $json, string $fileName): ImportRun
    {
        MigrationRequired::unlessMigrated($this->migration);

        if ($this->isRunning()) {
            throw new RuntimeException(__('An import is already running.', 'product-specifications'));
        }

        $package = $this->reader->read($json);
        $plan = $this->planner->plan($package);

        $this->discard();

        $run = ImportRun::planned(
            $fileName,
            $this->storage->store($json),
            $plan->unknownSections(),
            ImportSummary::ofPlan($plan),
            $plan->count()
        );
        $this->storage->write($run->planFile(), (string) wp_json_encode($plan->toArray()));
        $this->runs->store($run);

        return $run;
    }

    public function start(): ?ImportRun
    {
        $run = $this->runs->stored();

        if ($run !== null && in_array($run->status(), [ImportRun::PLANNED, ImportRun::FAILED], true)) {
            $run = $run->started();
            $this->runs->store($run);
        }

        return $run;
    }

    public function discard(): void
    {
        $run = $this->runs->stored();

        if ($run === null || $run->isRunning()) {
            return;
        }

        $this->storage->delete($run->storedFile());
        $this->storage->delete($run->planFile());
        $this->storage->delete($run->reportFile());
        $this->runs->delete();
    }

    /**
     * Every recorded outcome of a finished run, by section and entry index. An entry recorded
     * twice, because a chunk stopped between writing it and saving the run, keeps its last outcome.
     *
     * @return array<string, array<int, array{status: string, label: string, notes: list<string>}>>|null
     */
    public function report(): ?array
    {
        $run = $this->runs->stored();

        if ($run === null || !$run->isComplete()) {
            return null;
        }

        $report = [];
        $lines = explode("\n", $this->storage->read($run->reportFile()));

        foreach ($lines as $line) {
            $recorded = json_decode($line, true);
            $section = is_array($recorded) ? ($recorded['section'] ?? null) : null;
            $index = is_array($recorded) ? ($recorded['index'] ?? null) : null;

            if (is_array($recorded) && is_string($section) && is_int($index)) {
                /** @var array<string, mixed> $recorded */
                $report[$section][$index] = EntryOutcome::fromArray($recorded)->toArray();
            }
        }

        return $report;
    }

    public function runChunk(): void
    {
        $run = $this->runs->stored();

        if ($run === null || !$run->isRunning()) {
            return;
        }

        try {
            [$package, $plan] = $this->loaded($run);
            $next = $this->importer->importFrom(
                $package,
                $plan,
                ImportCursor::fromArray($run->cursor() ?? []),
                self::CHUNK_SIZE,
                function (
                    string $sectionKey,
                    int $index,
                    EntryOutcome $outcome,
                    ImportCursor $after
                ) use (&$run): void {
                    $this->storage->append(
                        $run->reportFile(),
                        wp_json_encode(['section' => $sectionKey, 'index' => $index, ...$outcome->toArray()]) . "\n"
                    );
                    $run = $run->recorded($sectionKey, $outcome, $after->toArray());
                    $this->runs->store($run);
                }
            );

            if ($next === null) {
                $this->storage->delete($run->storedFile());
                $this->storage->delete($run->planFile());
                $this->loaded = null;
                $run = $run->completed();
            } else {
                $run = $run->at($next->toArray());
            }
        } catch (Throwable $throwable) {
            $run = $run->failed($throwable->getMessage());
        }

        $this->runs->store($run);
    }

    /**
     * Chunks run back to back in one request, so the package and its plan are read once.
     *
     * @return array{Package, ImportPlan}
     */
    private function loaded(ImportRun $run): array
    {
        if ($this->loaded === null || $this->loaded[0] !== $run->storedFile()) {
            /** @var array<string, mixed>|null $plan */
            $plan = json_decode($this->storage->read($run->planFile()), true);

            $this->loaded = [
                $run->storedFile(),
                $this->reader->readPlanned($this->storage->read($run->storedFile())),
                ImportPlan::fromArray(is_array($plan) ? $plan : []),
            ];
        }

        return [$this->loaded[1], $this->loaded[2]];
    }
}
