<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Assets;

use RuntimeException;

/**
 * @psalm-type AssetArguments = array{dependencies?: array<string>, in_footer?: bool, strategy?: 'defer' | 'async'}
 */
final class AssetHelper
{
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
        $this->registerAsset(
            $handle,
            $relativePath,
            array_merge(
                $args,
                [
                    'in_footer' => true, // Put scripts in footer by default
                ]
            )
        );
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
