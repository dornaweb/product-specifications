<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Admin;

use Amiut\ProductSpecs\Assets\AssetHelper;

final class Assets
{
    public const HANDLE_ADMIN_SCRIPT = 'product-specifications-admin-general';

    private AssetHelper $assetHelper;

    public function __construct(
        AssetHelper $assetHelper
    ) {

        $this->assetHelper = $assetHelper;
    }

    public function load(): void
    {
        wp_enqueue_script('jquery-ui-sortable');

        $this->assetHelper->registerScript(self::HANDLE_ADMIN_SCRIPT, 'admin.js');
        $this->assetHelper->registerStyle(self::HANDLE_ADMIN_SCRIPT, 'admin.css');

        wp_localize_script(
            self::HANDLE_ADMIN_SCRIPT,
            'dwspecs_plugin',
            [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'i18n' => [
                    'importing_message' => esc_html__(
                        'Importing data may take a long time, please wait...',
                        'product-specifications'
                    ),
                    'unknown_error' => esc_html__('Something went wrong', 'product-specifications'),
                ],
            ]
        );

        wp_enqueue_script(self::HANDLE_ADMIN_SCRIPT);
        wp_enqueue_style(self::HANDLE_ADMIN_SCRIPT);
    }
}
