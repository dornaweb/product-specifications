<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Integration\WooCommerce;

use Amiut\ProductSpecs\Block\CompareButton\CompareButtonBlock;
use Amiut\ProductSpecs\Comparison\ComparisonSettings;
use Amiut\ProductSpecs\Settings\SettingsRepository;
use WC_Product;

/**
 * Both actions fire once in classic product templates and in WooCommerce's block
 * templates, including the add-to-cart-with-options block. WooCommerce also fires the
 * classic summary action inside block templates, so hooking it would show the button twice.
 */
final class CompareButtonPlacement
{
    private const ACTIONS = [
        ComparisonSettings::PLACEMENT_AFTER_ADD_TO_CART => 'woocommerce_after_add_to_cart_form',
        ComparisonSettings::PLACEMENT_AFTER_META => 'woocommerce_product_meta_end',
    ];

    public function __construct(
        private readonly CompareButtonBlock $block,
        private readonly SettingsRepository $settings
    ) {
    }

    public function register(): void
    {
        $action = self::ACTIONS[$this->settings->current()->compareButtonPlacement()] ?? null;

        if ($action !== null) {
            add_action($action, [$this, 'display']);
        }
    }

    public function display(): void
    {
        // These actions pass no arguments. WooCommerce sets the product being rendered as
        // this global, including a product an add-to-cart block takes from its context.
        $product = $GLOBALS['product'] ?? null;

        if ($product instanceof WC_Product) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The block escapes its output.
            echo $this->block->renderFor($product);
        }
    }
}
