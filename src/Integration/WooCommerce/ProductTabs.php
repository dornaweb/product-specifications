<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Integration\WooCommerce;

use Amiut\ProductSpecs\Repository\SpecificationsTableRepository;
use Amiut\ProductSpecs\Settings\SettingsRepository;
use Amiut\ProductSpecs\Upgrade\MigrationState;
use WC_Product;

class ProductTabs
{
    private SpecificationsTableRepository $repository;

    public function __construct(
        SpecificationsTableRepository $repository,
        private readonly SettingsRepository $settingsRepository,
        private readonly MigrationState $migration
    ) {
        $this->repository = $repository;
    }

    /**
     * @wp-hook woocommerce_product_tabs
     */
    public function addProductSpecificationsTab(array $tabs): array
    {
        if ($this->migration->requiresMigration()) {
            return $tabs;
        }

        $product = wc_get_product();

        if (!$product instanceof WC_Product) {
            return $tabs;
        }

        $hasSpecifications = $this->repository->productHasSpecsTable($product->get_id());
        $settings = $this->settingsRepository->current();

        if ($hasSpecifications) {
            $tabs['dwspecs_product_specifications'] = [
                'title' => $settings->tabTitle(),
                'priority' => 10,
                'callback' => [$this, 'displayTable'],
            ];
        }

        if (
            $settings->wooCommerceDefaultSpecifications() === 'remove'
            || (
                $hasSpecifications
                && $settings->wooCommerceDefaultSpecifications() === 'remove_if_specs_not_empty'
            )
        ) {
            unset($tabs['additional_information']);
        }

        return $tabs;
    }

    public function displayTable(): void
    {
        echo do_shortcode('[specs-table]');
    }
}
