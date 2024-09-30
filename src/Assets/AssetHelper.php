<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Assets;

use RuntimeException;

/**
 * @psalm-type AssetArguments = array{in_footer?: bool, strategy?: 'defer' | 'async'}
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

    public function registerStyle(string $handle, string $relativePath): void
    {
        $this->registerAsset($handle, $relativePath, [], Asset::TYPE_STYLE);
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

        if ($type === Asset::TYPE_SCRIPT) {
            wp_register_script(
                $handle,
                $this->assetsDirectoryUrl . '/' . $relativePath,
                $assetData['dependencies'] ?? [],
                $assetData['version'] ?? false,
                $args
            );
            return;
        }

        wp_register_style(
            $handle,
            $this->assetsDirectoryUrl . '/' . $relativePath,
            $assetData['dependencies'] ?? [],
            $assetData['version'] ?? false
        );
    }

    /**
     * @return array{dependencies?: array<string>, version?: string}
     */
    private function wpScriptsGeneratedData(string $assetPath): array
    {
        $info = pathinfo($assetPath);
        $wpScriptMetaFile = sprintf(
            '%s/%s%s',
            $info['dirname'],
            $info['filename'],
            '.asset.php'
        );

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
