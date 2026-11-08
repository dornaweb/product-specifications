<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration;

use Amiut\ProductSpecs\Background\BackgroundWorker;

final class BackgroundMigration
{
    public const HOOK = 'product_specifications_migration_chunk';
    public const TIME_BUDGET = BackgroundWorker::TIME_BUDGET;
    public const OVERDUE_AFTER = BackgroundWorker::OVERDUE_AFTER;

    private readonly BackgroundWorker $worker;

    public function __construct(
        private readonly MigrationRunner $runner,
        float $timeBudget = self::TIME_BUDGET
    ) {
        $this->worker = new BackgroundWorker(
            self::HOOK,
            fn (): bool => $this->runner->run()->isRunning(),
            function (): void {
                $this->runner->runChunk();
            },
            $timeBudget
        );
    }

    public function start(): MigrationRun
    {
        $run = $this->runner->start();
        $this->worker->schedule();

        return $run;
    }

    public function resume(): MigrationRun
    {
        $run = $this->runner->resume();
        $this->worker->schedule();

        return $run;
    }

    public function process(): void
    {
        $this->worker->process();
    }

    public function nudge(): MigrationRun
    {
        $this->worker->nudge();

        return $this->runner->run();
    }
}
