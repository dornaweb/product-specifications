<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport;

use Amiut\ProductSpecs\Upgrade\MigrationState;
use WP_CLI;

final class PackageCommand
{
    public const EXPORT = 'product-specifications export';
    public const IMPORT = 'product-specifications import';

    public function __construct(
        private readonly ExportDownload $download,
        private readonly PackageReader $reader,
        private readonly ImportPlanner $planner,
        private readonly PackageImporter $importer,
        private readonly MigrationState $migration
    ) {
    }

    /**
     * Exports specifications, sets and product values to a JSON file.
     *
     * ## OPTIONS
     *
     * [--without-values]
     * : Leave product values out.
     *
     * [--file=<path>]
     * : Where to write the file. Defaults to a dated file in the current directory.
     *
     * @param list<string> $args
     * @param array<string, string|bool> $assocArgs
     */
    public function export(array $args, array $assocArgs): void
    {
        $this->requireMigrated();

        $file = is_string($assocArgs['file'] ?? null) ? $assocArgs['file'] : $this->download->fileName();
        // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPressVIPMinimum.Functions.RestrictedFunctions -- A CLI write to a path the user chose.
        $handle = fopen($file, 'wb');

        if ($handle === false) {
            WP_CLI::error(sprintf(
                /* translators: %s: a file path. */
                __('Cannot write to %s.', 'product-specifications'),
                $file
            ));
        }

        $counts = $this->download->export(
            !isset($assocArgs['without-values']),
            static function (string $chunk) use ($handle): void {
                // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- See fopen() above.
                fwrite($handle, $chunk);
            }
        );

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- See fopen() above.
        fclose($handle);

        foreach ($counts as $section => $count) {
            WP_CLI::log(sprintf('%s: %d', $section, $count));
        }

        WP_CLI::success(sprintf(
            /* translators: %s: a file path. */
            __('Exported to %s.', 'product-specifications'),
            $file
        ));
    }

    /**
     * Imports a package file made by the export.
     *
     * ## OPTIONS
     *
     * <file>
     * : The package file.
     *
     * [--dry-run]
     * : Show what the import would do, and change nothing.
     *
     * @param list<string> $args
     * @param array<string, string|bool> $assocArgs
     */
    public function import(array $args, array $assocArgs): void
    {
        $this->requireMigrated();

        $file = $args[0] ?? '';
        // phpcs:ignore WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsUnknown -- A CLI read of a path the user chose.
        $json = is_readable($file) ? file_get_contents($file) : false;

        if ($json === false) {
            WP_CLI::error(sprintf(
                /* translators: %s: a file path. */
                __('Cannot read %s.', 'product-specifications'),
                $file
            ));
        }

        try {
            $package = $this->reader->read($json);
        } catch (InvalidPackage $invalid) {
            foreach ($invalid->errors() as $error) {
                WP_CLI::warning($error);
            }

            WP_CLI::error(__('The file cannot be imported.', 'product-specifications'));
        }

        $plan = $this->planner->plan($package);

        foreach ($plan->unknownSections() as $section) {
            WP_CLI::warning(sprintf(
                /* translators: %s: a section key. */
                __('Section "%s" is not known to this site and is left out.', 'product-specifications'),
                $section
            ));
        }

        if (isset($assocArgs['dry-run'])) {
            $this->summarize($plan->toArray()['sections']);
            WP_CLI::success(__('Dry run: nothing was changed.', 'product-specifications'));

            return;
        }

        $report = $this->importer->import($package, $plan);
        $outcomes = [];

        foreach ($package->sectionKeys() as $section) {
            foreach ($report->outcomes($section) as $index => $outcome) {
                $outcomes[$section][$index] = $outcome->toArray();
            }
        }

        $this->summarize($outcomes);
        WP_CLI::success(__('Imported.', 'product-specifications'));
    }

    /**
     * @param array<string, array<int, array{status: string, label: string, notes: list<string>}>> $outcomes
     */
    private function summarize(array $outcomes): void
    {
        foreach ($outcomes as $section => $entries) {
            $counts = [];

            foreach ($entries as $entry) {
                $counts[$entry['status']] = ($counts[$entry['status']] ?? 0) + 1;

                foreach ($entry['notes'] as $note) {
                    WP_CLI::log(sprintf('%s %s [%s]: %s', $section, $entry['label'], $entry['status'], $note));
                }
            }

            WP_CLI::log(sprintf(
                '%s: %s',
                $section,
                implode(', ', array_map(
                    static fn (string $status, int $count): string => "{$status} {$count}",
                    array_keys($counts),
                    $counts
                ))
            ));
        }
    }

    private function requireMigrated(): void
    {
        try {
            MigrationRequired::unlessMigrated($this->migration);
        } catch (MigrationRequired $required) {
            WP_CLI::error($required->getMessage());
        }
    }
}
