<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Integration\WooCommerce;

use Amiut\ProductSpecs\Assets\AssetHelper;

class Assets
{
    public const HANDLE_FRONTEND_STYLE = 'product-specifications-frontend';

    private AssetHelper $assetHelper;

    public function __construct(
        AssetHelper $assetHelper
    ) {

        $this->assetHelper = $assetHelper;
    }

    public function load(): void
    {
        if (get_option('dwps_disable_default_styles') === 'on') {
            return;
        }

        if (!is_singular('product')) {
            return;
        }

        $this->assetHelper->registerStyle(
            self::HANDLE_FRONTEND_STYLE,
            'frontend.css'
        );
        wp_enqueue_style(self::HANDLE_FRONTEND_STYLE);
    }
}
