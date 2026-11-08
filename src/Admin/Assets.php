<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Admin;

use Amiut\ProductSpecs\Assets\AssetHelper;
use Amiut\ProductSpecs\Capability\Capabilities;
use Amiut\ProductSpecs\Migration\MigrationNotice;
use Amiut\ProductSpecs\ImportExport\ExportDownload;
use Amiut\ProductSpecs\ImportExport\Import\ImportRestController;
use Amiut\ProductSpecs\Migration\MigrationRestController;
use Amiut\ProductSpecs\Settings\SettingsRestController;
use Amiut\ProductSpecs\Upgrade\MigrationState;

final class Assets
{
    public const HANDLE_ADMIN_INIT_SCRIPT = 'product-specifications-admin-init';

    public function __construct(
        private readonly AssetHelper $assetHelper,
        private readonly Capabilities $capabilities,
        private readonly MigrationState $migration,
        private readonly MigrationNotice $migrationNotice,
        private readonly ExportDownload $exportDownload
    ) {
    }

    public function load(string $hookSuffix): void
    {
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
                'migrationRequired' => $this->migration->requiresMigration(),
                'migrationEndpoint' => rest_url(MigrationRestController::NAMESPACE . MigrationRestController::ROUTE),
                'migrationNotice' => $this->migrationNotice->content(),
                'exportUrl' => $this->exportDownload->url(),
                'importEndpoint' => rest_url(ImportRestController::NAMESPACE . ImportRestController::ROUTE),
                'restNonce' => wp_create_nonce('wp_rest'),
            ]
        );

        wp_enqueue_script(self::HANDLE_ADMIN_INIT_SCRIPT);
        wp_enqueue_style(self::HANDLE_ADMIN_INIT_SCRIPT);
    }
}
