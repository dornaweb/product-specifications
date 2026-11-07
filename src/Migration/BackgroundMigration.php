<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration;

final class BackgroundMigration
{
    public const HOOK = 'product_specifications_migration_chunk';
    public const TIME_BUDGET = 20.0;
    public const OVERDUE_AFTER = 60;

    public function __construct(
        private readonly MigrationRunner $runner,
        private readonly float $timeBudget = self::TIME_BUDGET
    ) {
    }

    public function start(): MigrationRun
    {
        return $this->scheduled($this->runner->start());
    }

    public function resume(): MigrationRun
    {
        return $this->scheduled($this->runner->resume());
    }

    /**
     * The next event is queued before any work, because a request killed mid-chunk by a
     * memory or time limit never reaches code after it. An event left queued after the
     * run ends finds nothing to do.
     */
    public function process(): void
    {
        $run = $this->scheduled($this->runner->run());

        if (!$run->isRunning()) {
            return;
        }

        $deadline = microtime(true) + $this->budget();

        do {
            $run = $this->runner->runChunk();
        } while ($run->isRunning() && microtime(true) < $deadline);
    }

    /**
     * Called while someone watches the run. WP-Cron never fires on a site that disables
     * it without a server cron or blocks loopback requests, so an event long overdue means
     * nothing will carry the run, and the caller carries one chunk instead.
     */
    public function nudge(): MigrationRun
    {
        $run = $this->scheduled($this->runner->run());
        $next = wp_next_scheduled(self::HOOK);

        if ($run->isRunning() && is_int($next) && $next < time() - self::OVERDUE_AFTER) {
            return $this->runner->runChunk();
        }

        return $run;
    }

    private function scheduled(MigrationRun $run): MigrationRun
    {
        if ($run->isRunning() && wp_next_scheduled(self::HOOK) === false) {
            wp_schedule_single_event(time(), self::HOOK);
        }

        return $run;
    }

    private function budget(): float
    {
        $limit = (int) ini_get('max_execution_time');

        return $limit > 0 ? min($this->timeBudget, $limit / 2) : $this->timeBudget;
    }
}
