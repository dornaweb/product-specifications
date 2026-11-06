<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ProductValues;

use Amiut\ProductSpecs\Assets\AssetHelper;

final class Assets
{
    public const HANDLE = 'product-specifications-product-values';

    public function __construct(private readonly AssetHelper $assets)
    {
    }

    public function load(string $hookSuffix): void
    {
        if ($hookSuffix !== 'post.php' && $hookSuffix !== 'post-new.php') {
            return;
        }

        $screen = get_current_screen();

        if ($screen === null || $screen->post_type !== 'product') {
            return;
        }

        $this->assets->registerScript(self::HANDLE, 'product-values.js');
        $this->assets->registerStyle(self::HANDLE, 'product-values.css', ['wp-components']);

        wp_enqueue_script(self::HANDLE);
        wp_enqueue_style(self::HANDLE);
    }
}
