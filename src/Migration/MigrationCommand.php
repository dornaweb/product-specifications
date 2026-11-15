<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration;

use WP_CLI;

final class MigrationCommand
{
    public const NAME = 'product-specifications migration';

    public function __construct(private readonly MigrationRunner $runner)
    {
    }

    /**
     * Migrates the site's specifications to the end, resuming a run that failed. A run
     * the background job is carrying is taken over, so a site whose cron never fires can
     * still finish.
     */
    public function run(): void
    {
        wp_clear_scheduled_hook(BackgroundMigration::HOOK);

        $run = $this->runner->run();
        $run = $run->isFailed() ? $this->runner->resume() : $this->runner->start();

        if (!$run->isRunning()) {
            WP_CLI::success(__('There is nothing to migrate.', 'product-specifications'));
            return;
        }

        while ($run->isRunning()) {
            $run = $this->runner->runChunk();
            WP_CLI::log($this->progress($run));
        }

        $this->report($run);

        if ($run->isFailed()) {
            WP_CLI::error(
                sprintf(
                    /* translators: %s: why the migration stopped. */
                    __('The migration stopped: %s', 'product-specifications'),
                    (string) $run->failure()
                )
            );
        }

        WP_CLI::success(__('The migration is complete.', 'product-specifications'));
    }

    /**
     * Prints the current migration run without changing it.
     */
    public function status(): void
    {
        $run = $this->runner->run();

        WP_CLI::log(
            sprintf(
                /* translators: %s: the migration status, such as running or failed. */
                __('Status: %s', 'product-specifications'),
                $run->status()
            )
        );
        WP_CLI::log($this->progress($run));

        if ($run->failure() !== null) {
            WP_CLI::log(
                sprintf(
                    /* translators: %s: why the migration stopped. */
                    __('Stopped: %s', 'product-specifications'),
                    $run->failure()
                )
            );
        }

        $this->report($run);
    }

    private function progress(MigrationRun $run): string
    {
        return sprintf(
            /* translators: 1: items processed, 2: items in total. */
            __('Processed %1$d of %2$d.', 'product-specifications'),
            $run->processed(),
            $run->total()
        );
    }

    private function report(MigrationRun $run): void
    {
        foreach ($run->skipped() as $entry) {
            WP_CLI::log(
                sprintf(
                    /* translators: 1: a migration step, 2: an item id. */
                    __('Skipped %1$s %2$d: it already holds data in the current format.', 'product-specifications'),
                    $entry['step'],
                    $entry['item']
                )
            );
        }

        foreach ($run->errors() as $entry) {
            WP_CLI::warning(sprintf('%1$s %2$d: %3$s', $entry['step'], $entry['item'], $entry['message']));
        }
    }
}
