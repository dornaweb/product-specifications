<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Assets;

use RuntimeException;

/**
 * @psalm-type AssetArguments = array{dependencies?: array<string>, in_footer?: bool, strategy?: 'defer' | 'async'}
 */
final class AssetHelper
{
    private const TEXT_DOMAIN = 'product-specifications';
    private const CHUNKS = 'chunks';

    private string $assetsDirectoryPath;
    private string $assetsDirectoryUrl;

    public function __construct(
        string $assetsDirectoryPath,
        string $assetsDirectoryUrl
    ) {

        $this->assetsDirectoryPath = rtrim($assetsDirectoryPath, '/');
        $this->assetsDirectoryUrl = rtrim($assetsDirectoryUrl, '/');
    }

    /**
     * @param AssetArguments $args
     */
    public function registerScript(string $handle, string $relativePath, array $args = []): void
    {
        $this->registerAsset($handle, $relativePath, array_merge(['in_footer' => true], $args));
        wp_set_script_translations($handle, self::TEXT_DOMAIN);

        if ($this->loadsChunks($relativePath)) {
            $this->addChunkTranslations($handle);
        }
    }

    /**
     * @param list<string> $dependencies Style handles only.
     */
    public function registerStyle(
        string $handle,
        string $relativePath,
        array $dependencies = []
    ): void {
        $this->registerAsset(
            $handle,
            $relativePath,
            ['dependencies' => $dependencies],
            Asset::TYPE_STYLE
        );
    }

    /**
     * Webpack writes the chunk directory into the loader of every entry that imports
     * code lazily.
     */
    private function loadsChunks(string $relativePath): bool
    {
        $source = file_get_contents($this->assetsDirectoryPath . '/' . ltrim($relativePath, '/'));

        return is_string($source) && str_contains($source, '"' . self::CHUNKS . '/"');
    }

    /**
     * WordPress loads translations only for registered scripts, and webpack chunks load
     * later in the browser, so each chunk's translations are added before the entry runs.
     */
    private function addChunkTranslations(string $entryHandle): void
    {
        foreach (glob($this->assetsDirectoryPath . '/' . self::CHUNKS . '/*.js') ?: [] as $chunkPath) {
            $chunkFile = self::CHUNKS . '/' . basename($chunkPath);
            $chunkHandle = $entryHandle . '-chunk-' . basename($chunkPath, '.js');
            wp_register_script($chunkHandle, $this->assetsDirectoryUrl . '/' . $chunkFile);
            wp_set_script_translations($chunkHandle, self::TEXT_DOMAIN);
            $translations = wp_scripts()->print_translations($chunkHandle, false);

            if (is_string($translations)) {
                wp_add_inline_script($entryHandle, $translations, 'before');
            }
        }
    }

    /**
     * @param AssetArguments $args
     */
    private function registerAsset(string $handle, string $relativePath, array $args = [], string $type = Asset::TYPE_SCRIPT): void
    {
        $relativePath = ltrim($relativePath, '/');
        $assetPath = $this->assetsDirectoryPath . '/' . $relativePath;

        if (!file_exists($assetPath)) {
            throw new RuntimeException(
                sprintf(
                    'Could not find file %s',
                    esc_html($assetPath),
                )
            );
        }

        $assetData = $this->wpScriptsGeneratedData($assetPath);

        // A stylesheet never inherits the .asset.php dependencies: those are
        // script handles, and WordPress silently refuses to enqueue a style
        // whose dependency is not a registered style.
        $styleDependencies = $args['dependencies'] ?? [];

        $dependencies = array_values(
            array_unique(
                array_merge(
                    $assetData['dependencies'] ?? [],
                    $args['dependencies'] ?? []
                )
            )
        );
        unset($args['dependencies']);

        if ($type === Asset::TYPE_SCRIPT) {
            wp_register_script(
                $handle,
                $this->assetsDirectoryUrl . '/' . $relativePath,
                $dependencies,
                $assetData['version'] ?? false,
                $args
            );
            return;
        }

        wp_register_style(
            $handle,
            $this->assetsDirectoryUrl . '/' . $relativePath,
            $styleDependencies,
            $assetData['version'] ?? false
        );

        if (file_exists(preg_replace('/\.css$/', '-rtl.css', $assetPath) ?? '')) {
            wp_style_add_data($handle, 'rtl', 'replace');
        }
    }

    /**
     * @return array{dependencies?: array<string>, version?: string}
     */
    private function wpScriptsGeneratedData(string $assetPath): array
    {
        $directory = pathinfo($assetPath, PATHINFO_DIRNAME);
        $filename = pathinfo($assetPath, PATHINFO_FILENAME);
        $wpScriptMetaFile = sprintf('%s/%s.asset.php', $directory, $filename);

        if (!file_exists($wpScriptMetaFile)) {
            return [];
        }

        $info = include $wpScriptMetaFile;

        if (!is_array($info)) {
            return [];
        }

        /** @var array{dependencies?: array<string>, version?: string} */
        return $info;
    }
}
