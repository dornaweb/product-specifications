<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Admin;

use Amiut\ProductSpecs\Assets\AssetHelper;
use Amiut\ProductSpecs\Capability\Capabilities;
use Amiut\ProductSpecs\Settings\SettingsRestController;

final class Assets
{
    public const HANDLE_ADMIN_SCRIPT = 'product-specifications-admin-general';
    public const HANDLE_ADMIN_INIT_SCRIPT = 'product-specifications-admin-init';

    public function __construct(
        private readonly AssetHelper $assetHelper,
        private readonly Capabilities $capabilities
    ) {
    }

    public function load(string $hookSuffix): void
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

        if ($hookSuffix !== 'woocommerce_page_wc-admin') {
            return;
        }

        $this->assetHelper->registerScript(self::HANDLE_ADMIN_INIT_SCRIPT, 'admininit.js');
        $this->assetHelper->registerStyle(
            self::HANDLE_ADMIN_INIT_SCRIPT,
            'admininit.css',
            ['wp-components']
        );

        wp_localize_script(
            self::HANDLE_ADMIN_INIT_SCRIPT,
            'dwspecsAdmin',
            [
                'capability' => $this->capabilities->manageSpecifications(),
                'settingsCapability' => current_user_can(SettingsRestController::READ_CAPABILITY)
                    ? SettingsRestController::READ_CAPABILITY
                    : $this->capabilities->manageSpecifications(),
                'settingsEndpoint' => rest_url('product-specifications/v2/settings'),
                'restNonce' => wp_create_nonce('wp_rest'),
            ]
        );

        wp_enqueue_script(self::HANDLE_ADMIN_INIT_SCRIPT);
        wp_enqueue_style(self::HANDLE_ADMIN_INIT_SCRIPT);
    }
}
