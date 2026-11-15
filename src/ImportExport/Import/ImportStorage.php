<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport\Import;

use RuntimeException;

/**
 * Keeps an uploaded package and the files its import writes, in a directory the web
 * server is told not to serve.
 */
final class ImportStorage
{
    private const DIRECTORY = 'product-specifications-imports';

    public function store(string $contents): string
    {
        $directory = $this->directory();
        $path = $directory . '/' . wp_generate_password(32, false) . '.json';

        // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPressVIPMinimum.Functions.RestrictedFunctions -- A private upload.
        if (file_put_contents($path, $contents) === false) {
            throw new RuntimeException(__('The file could not be stored for importing.', 'product-specifications'));
        }

        return $path;
    }

    public function write(string $path, string $contents): void
    {
        // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPressVIPMinimum.Functions.RestrictedFunctions -- A private file.
        if (!$this->owns($path) || file_put_contents($path, $contents) === false) {
            throw new RuntimeException(__('The import could not write its files.', 'product-specifications'));
        }
    }

    public function append(string $path, string $contents): void
    {
        // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPressVIPMinimum.Functions.RestrictedFunctions -- A private file.
        if (!$this->owns($path) || file_put_contents($path, $contents, FILE_APPEND | LOCK_EX) === false) {
            throw new RuntimeException(__('The import could not write its files.', 'product-specifications'));
        }
    }

    public function read(string $path): string
    {
        // phpcs:ignore WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsUnknown -- A local file.
        $contents = $this->owns($path) && is_readable($path) ? file_get_contents($path) : false;

        if ($contents === false) {
            throw new RuntimeException(
                __('The uploaded file is no longer available. Upload it again.', 'product-specifications')
            );
        }

        return $contents;
    }

    public function delete(string $path): void
    {
        if ($this->owns($path) && file_exists($path)) {
            wp_delete_file($path);
        }
    }

    private function owns(string $path): bool
    {
        return $path !== '' && str_starts_with($path, $this->directory() . '/');
    }

    private function directory(): string
    {
        $uploads = wp_upload_dir(null, false);
        $directory = rtrim((string) $uploads['basedir'], '/') . '/' . self::DIRECTORY;

        if (!is_dir($directory)) {
            wp_mkdir_p($directory);
            // phpcs:disable WordPress.WP.AlternativeFunctions, WordPressVIPMinimum.Functions.RestrictedFunctions
            file_put_contents($directory . '/index.php', "<?php\n");
            file_put_contents($directory . '/.htaccess', "Require all denied\nDeny from all\n");
            // phpcs:enable WordPress.WP.AlternativeFunctions, WordPressVIPMinimum.Functions.RestrictedFunctions
        }

        return $directory;
    }
}
