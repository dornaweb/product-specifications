<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Integration\WooCommerce;

use Amiut\ProductSpecs\Assets\AssetHelper;
use Amiut\ProductSpecs\Settings\SettingsRepository;

class Assets
{
    public const HANDLE_FRONTEND_STYLE = 'product-specifications-frontend';

    private AssetHelper $assetHelper;

    public function __construct(
        AssetHelper $assetHelper,
        private readonly SettingsRepository $settingsRepository
    ) {

        $this->assetHelper = $assetHelper;
    }

    public function load(): void
    {
        if (!$this->settingsRepository->current()->loadDefaultStyles()) {
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
