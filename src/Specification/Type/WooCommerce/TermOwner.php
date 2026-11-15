<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\WooCommerce;

use WC_Product;
use WC_Product_Variation;

/**
 * Terms and attributes belong to the parent product. A variation's own attribute is the
 * option it was created for, not the product's list.
 */
final class TermOwner
{
    public static function of(WC_Product $product): WC_Product
    {
        if (!$product instanceof WC_Product_Variation) {
            return $product;
        }

        $parent = wc_get_product($product->get_parent_id());

        return $parent instanceof WC_Product ? $parent : $product;
    }
}
