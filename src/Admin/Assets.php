<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Admin;

use Amiut\ProductSpecs\Assets\AssetHelper;
use Amiut\ProductSpecs\Migration\MigrationNotice;
use Amiut\ProductSpecs\ImportExport\ExportDownload;
use Amiut\ProductSpecs\ImportExport\Import\ImportRestController;
use Amiut\ProductSpecs\Migration\MigrationRestController;
use Amiut\ProductSpecs\Migration\MigrationState;
use Amiut\ProductSpecs\Specification\Type\WooCommerce\ProductDataChoicesScript;

final class Assets
{
    public const HANDLE_ADMIN_INIT_SCRIPT = 'product-specifications-admin-init';

    public function __construct(
        private readonly AssetHelper $assetHelper,
        private readonly AdminScreens $screens,
        private readonly MigrationState $migration,
        private readonly MigrationNotice $migrationNotice,
        private readonly ExportDownload $exportDownload,
        private readonly ProductDataChoicesScript $productDataChoices
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
            'productSpecificationsAdmin',
            [
                'pages' => array_map(
                    static fn (Page $page): array => [
                        'path' => $page->path(),
                        'title' => $page->title(),
                        'capability' => $page->capability(),
                    ],
                    $this->screens->pages()
                ),
                'settingsEndpoint' => rest_url('product-specifications/v2/settings'),
                'migrationRequired' => $this->migration->requiresMigration(),
                'migrationEndpoint' => rest_url(MigrationRestController::NAMESPACE . MigrationRestController::ROUTE),
                'migrationNotice' => $this->migrationNotice->content(),
                'exportUrl' => $this->exportDownload->url(),
                'importEndpoint' => rest_url(ImportRestController::NAMESPACE . ImportRestController::ROUTE),
                'restNonce' => wp_create_nonce('wp_rest'),
            ]
        );

        $this->productDataChoices->attachTo(self::HANDLE_ADMIN_INIT_SCRIPT);

        wp_enqueue_script(self::HANDLE_ADMIN_INIT_SCRIPT);
        wp_enqueue_style(self::HANDLE_ADMIN_INIT_SCRIPT);
    }
}
