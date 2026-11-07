<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Integration\WooCommerce;

use Amiut\ProductSpecs\Settings\SettingsRepository;
use Amiut\ProductSpecs\Block\ProductSpecifications\ProductSpecificationsBlock;
use Amiut\ProductSpecs\Upgrade\MigrationState;
use WC_Product;

final class ProductTabs
{
    public function __construct(
        private readonly ProductSpecificationsBlock $block,
        private readonly SettingsRepository $settingsRepository,
        private readonly MigrationState $migration
    ) {
    }

    /**
     * @wp-hook woocommerce_product_tabs
     *
     * @param array<string, mixed> $tabs
     * @return array<string, mixed>
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

        $hasSpecifications = $this->block->hasTableFor($product);
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
        $product = wc_get_product();

        if ($product instanceof WC_Product) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The block template escapes its output.
            echo $this->block->renderFor($product->get_id());
        }
    }
}
