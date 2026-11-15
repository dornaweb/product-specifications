<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Background;

use Closure;

/**
 * Carries chunked work across WP-Cron events. The work decides what a chunk is and when
 * it is finished; the worker decides when chunks run.
 */
final class BackgroundWorker
{
    public const TIME_BUDGET = 20.0;
    public const OVERDUE_AFTER = 60;

    /**
     * @param Closure(): bool $isRunning
     * @param Closure(): void $runChunk
     */
    public function __construct(
        private readonly string $hook,
        private readonly Closure $isRunning,
        private readonly Closure $runChunk,
        private readonly float $timeBudget = self::TIME_BUDGET
    ) {
    }

    public function hook(): string
    {
        return $this->hook;
    }

    public function schedule(): void
    {
        if (($this->isRunning)() && wp_next_scheduled($this->hook) === false) {
            wp_schedule_single_event(time(), $this->hook);
        }
    }

    /**
     * The next event is queued before any work, because a request killed mid-chunk by a
     * memory or time limit never reaches code after it. An event left queued after the
     * work ends finds nothing to do.
     */
    public function process(): void
    {
        $this->schedule();

        if (!($this->isRunning)()) {
            return;
        }

        $deadline = microtime(true) + $this->budget();

        do {
            ($this->runChunk)();
        } while (($this->isRunning)() && microtime(true) < $deadline);
    }

    /**
     * Called while someone watches the work. WP-Cron never fires on a site that disables
     * it without a server cron or blocks loopback requests, so an event long overdue means
     * nothing will carry the work, and the caller carries one chunk instead.
     */
    public function nudge(): void
    {
        $this->schedule();
        $next = wp_next_scheduled($this->hook);

        if (($this->isRunning)() && is_int($next) && $next < time() - self::OVERDUE_AFTER) {
            ($this->runChunk)();
        }
    }

    private function budget(): float
    {
        $limit = (int) ini_get('max_execution_time');

        return $limit > 0 ? min($this->timeBudget, $limit / 2) : $this->timeBudget;
    }
}
