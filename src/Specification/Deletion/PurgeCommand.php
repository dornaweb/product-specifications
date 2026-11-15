<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Deletion;

use WP_CLI;

final class PurgeCommand
{
    public const NAME = 'product-specifications purge';

    public function __construct(
        private readonly PurgeQueue $queue,
        private readonly BackgroundPurge $background
    ) {
    }

    /**
     * Removes the values of every deleted specification still queued, retrying the ones
     * that failed, so a site whose cron never fires can finish.
     */
    public function run(): void
    {
        wp_clear_scheduled_hook(BackgroundPurge::HOOK);
        $this->queue->retryAll();

        if ($this->queue->pending() === null) {
            WP_CLI::success(__('There is nothing to remove.', 'product-specifications'));

            return;
        }

        while (($entry = $this->queue->pending()) !== null) {
            $this->background->runChunk();
            WP_CLI::log(
                sprintf(
                    /* translators: %s: a specification key. */
                    __('Removing values of "%s".', 'product-specifications'),
                    $entry->slug()
                )
            );
        }

        $this->report();

        if ($this->queue->entries() !== []) {
            WP_CLI::error(__('Some values could not be removed.', 'product-specifications'));
        }

        WP_CLI::success(__('Every queued key has been removed from products.', 'product-specifications'));
    }

    /**
     * Prints the keys still being removed, without changing anything.
     */
    public function status(): void
    {
        if ($this->queue->entries() === []) {
            WP_CLI::log(__('No key is being removed.', 'product-specifications'));

            return;
        }

        $this->report();
    }

    private function report(): void
    {
        foreach ($this->queue->entries() as $entry) {
            $failure = $entry->failure();

            if ($failure === null) {
                WP_CLI::log(
                    sprintf(
                        /* translators: 1: a specification key, 2: a product id. */
                        __('%1$s: removed up to product %2$d.', 'product-specifications'),
                        $entry->slug(),
                        $entry->cursor()
                    )
                );
                continue;
            }

            WP_CLI::warning(sprintf('%1$s: %2$s', $entry->slug(), $failure));
        }
    }
}
