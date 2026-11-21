<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Comparison;

use WC_Product;

final class ProductHeader
{
    public const ACTION = 'product_specifications_comparison_product_header';

    public static function register(): void
    {
        add_action(self::ACTION, [self::class, 'thumbnail'], 10);
        add_action(self::ACTION, [self::class, 'title'], 20);
        add_action(self::ACTION, [self::class, 'price'], 30);
        add_action(self::ACTION, [self::class, 'stockStatus'], 40);
    }

    public static function thumbnail(WC_Product $product): void
    {
        printf(
            '<a class="ps-comparison__image" href="%1$s" tabindex="-1" aria-hidden="true">%2$s</a>',
            esc_url($product->get_permalink()),
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce escapes the image markup.
            $product->get_image('woocommerce_thumbnail')
        );
    }

    public static function title(WC_Product $product): void
    {
        printf(
            '<a class="ps-comparison__title" href="%1$s">%2$s</a>',
            esc_url($product->get_permalink()),
            esc_html($product->get_name())
        );
    }

    public static function price(WC_Product $product): void
    {
        $price = $product->get_price_html();

        if ($price !== '') {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce escapes the price markup.
            printf('<span class="ps-comparison__price">%s</span>', $price);
        }
    }

    public static function stockStatus(WC_Product $product): void
    {
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce escapes the stock markup.
        echo wc_get_stock_html($product);
    }
}
