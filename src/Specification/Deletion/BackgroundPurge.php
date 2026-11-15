<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Deletion;

use Amiut\ProductSpecs\Background\BackgroundWorker;

final class BackgroundPurge
{
    public const HOOK = 'product_specifications_purge_chunk';
    public const CHUNK_SIZE = 100;

    private readonly BackgroundWorker $worker;

    public function __construct(
        private readonly PurgeQueue $queue,
        private readonly ValuePurge $purge,
        float $timeBudget = BackgroundWorker::TIME_BUDGET
    ) {
        $this->worker = new BackgroundWorker(
            self::HOOK,
            fn (): bool => $this->queue->pending() !== null,
            function (): void {
                $this->runChunk();
            },
            $timeBudget
        );
    }

    public function schedule(): void
    {
        $this->worker->schedule();
    }

    public function process(): void
    {
        $this->worker->process();
    }

    public function nudge(): void
    {
        $this->worker->nudge();
    }

    /**
     * A key whose purge fails stays queued with the reason, so it stays taken, and is
     * retried from the command line.
     */
    public function runChunk(): void
    {
        $entry = $this->queue->pending();

        if ($entry === null) {
            return;
        }

        $progress = $this->purge->purge($entry->slug(), $entry->cursor(), self::CHUNK_SIZE);

        if ($progress->isFinished()) {
            $this->queue->remove($entry->slug());

            return;
        }

        $this->queue->advance($entry->slug(), $progress->cursor());

        if ($progress->failure() !== null) {
            $this->queue->recordFailure($entry->slug(), $progress->failure());
        }
    }
}
