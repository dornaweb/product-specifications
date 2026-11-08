<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport;

use Amiut\ProductSpecs\Capability\Capabilities;
use Amiut\ProductSpecs\ImportExport\Section\ValuesSection;
use Amiut\ProductSpecs\Upgrade\MigrationState;

final class ExportDownload
{
    public const ACTION = 'product_specifications_export';

    public function __construct(
        private readonly PackageExporter $exporter,
        private readonly SectionRegistry $sections,
        private readonly Capabilities $capabilities,
        private readonly MigrationState $migration
    ) {
    }

    public function url(): string
    {
        return wp_nonce_url(admin_url('admin-post.php?action=' . self::ACTION), self::ACTION);
    }

    /**
     * @return list<string>
     */
    public function sectionKeys(bool $withValues): array
    {
        return array_values(array_filter(
            $this->sections->keys(),
            static fn (string $key): bool => $withValues || $key !== ValuesSection::KEY
        ));
    }

    /**
     * @param callable(string): void $write
     * @return array<string, int>
     */
    public function export(bool $withValues, callable $write): array
    {
        return $this->exporter->export($this->sectionKeys($withValues), $write);
    }

    public function fileName(): string
    {
        $host = wp_parse_url(home_url(), PHP_URL_HOST);

        return sanitize_file_name(
            sprintf('product-specifications-%s-%s.json', is_string($host) ? $host : 'store', gmdate('Y-m-d'))
        );
    }

    public function handle(): void
    {
        if (!$this->capabilities->currentUserCanManage()) {
            wp_die(esc_html__('You are not allowed to export specifications.', 'product-specifications'), '', 403);
        }

        check_admin_referer(self::ACTION);

        try {
            MigrationRequired::unlessMigrated($this->migration);
        } catch (MigrationRequired $required) {
            wp_die(esc_html($required->getMessage()), '', 409);
        }

        $withValues = filter_input(INPUT_GET, 'values', FILTER_SANITIZE_SPECIAL_CHARS) !== '0';

        nocache_headers();
        header('Content-Type: application/json; charset=utf-8');
        header(sprintf('Content-Disposition: attachment; filename="%s"', $this->fileName()));

        $this->export(
            $withValues,
            static function (string $chunk): void {
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- A JSON download, encoded by wp_json_encode().
                echo $chunk;
            }
        );

        exit;
    }
}
