<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ProductValues;

use Amiut\ProductSpecs\Assets\AssetHelper;
use Amiut\ProductSpecs\Specification\Type\WooCommerce\ProductDataChoicesScript;

final class Assets
{
    public const HANDLE = 'product-specifications-product-values';

    public function __construct(
        private readonly AssetHelper $assets,
        private readonly ProductDataChoicesScript $productDataChoices
    ) {
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

        $this->productDataChoices->attachTo(self::HANDLE);

        wp_enqueue_media();
        wp_enqueue_script(self::HANDLE);
        wp_enqueue_style(self::HANDLE);
    }
}
