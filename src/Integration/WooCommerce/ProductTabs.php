<?php

namespace Amiut\ProductSpecs\Integration\WooCommerce;

use Amiut\ProductSpecs\Repository\SpecificationsTableRepository;
use WC_Product;

class ProductTabs
{
    private SpecificationsTableRepository $repository;

    public function __construct(SpecificationsTableRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @wp-hook woocommerce_product_tabs
     */
    public function addProductSpecificationsTab(array $tabs): array
    {
        $product = wc_get_product();

        if (! $product instanceof WC_Product) {
            return $tabs;
        }

        $hasSpecsTable = $this->repository->productHasSpecsTable($product->get_id());

        if ($hasSpecsTable) {
            $tabs['dwspecs_product_specifications'] = [
                'title' => get_option('dwps_tab_title') ?: esc_html__('Product Specifications', 'product-specifications'),
                'priority' => 10,
                'callback' => [$this, 'displayTable'],
            ];
        }

        if (
            get_option('dwps_wc_default_specs') === 'remove'
            || ($hasSpecsTable && get_option('dwps_wc_default_specs') === 'remove_if_specs_not_empty')
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
