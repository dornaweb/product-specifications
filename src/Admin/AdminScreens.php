<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Admin;

use Amiut\ProductSpecs\Capability\Capabilities;
use Amiut\ProductSpecs\Migration\MigrationState;
use Amiut\ProductSpecs\Settings\SettingsRestController;

/**
 * Every screen of the plugin's admin. WooCommerce builds a screen's title and
 * breadcrumbs only for paths registered on the server, so this list is the one
 * source for both the server and the client.
 */
final class AdminScreens
{
    public const PAGE_ID = 'product-specifications';
    public const ROOT_PATH = '/product-specifications';

    private const WOOCOMMERCE_MENU = 'woocommerce';
    private const MENU_ORDER = 50;

    public function __construct(
        private readonly Capabilities $capabilities,
        private readonly MigrationState $migration
    ) {
    }

    /**
     * @return list<Page>
     */
    public function pages(): array
    {
        $capability = $this->capabilities->manageSpecifications();

        $pages = [
            new Page(
                self::PAGE_ID,
                __('Product Specifications', 'product-specifications'),
                self::ROOT_PATH,
                self::WOOCOMMERCE_MENU,
                $capability,
                self::MENU_ORDER
            ),
            $this->child('specification-sets', __('Specification sets', 'product-specifications'), $capability),
            $this->child('specification-set/new', __('Specification set', 'product-specifications'), $capability),
            $this->child('specification-set/edit', __('Specification set', 'product-specifications'), $capability),
            $this->child('specifications', __('Specifications', 'product-specifications'), $capability),
            $this->child('import-export', __('Import / Export', 'product-specifications'), $capability),
            $this->child('settings', __('Settings', 'product-specifications'), $this->settingsCapability()),
        ];

        if ($this->migration->requiresMigration()) {
            $pages[] = $this->child('migration', __('Migration', 'product-specifications'), $capability);
        }

        return $pages;
    }

    private function child(string $path, string $title, string $capability): Page
    {
        return new Page(
            self::PAGE_ID . '-' . str_replace('/', '-', $path),
            $title,
            self::ROOT_PATH . '/' . $path,
            self::PAGE_ID,
            $capability,
            self::MENU_ORDER
        );
    }

    private function settingsCapability(): string
    {
        return current_user_can(SettingsRestController::READ_CAPABILITY)
            ? SettingsRestController::READ_CAPABILITY
            : $this->capabilities->manageSpecifications();
    }
}
