<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\WooCommerce;

use WC_Product;

final class WeightSource extends TextSource
{
    public function value(WC_Product $product): ?string
    {
        if (!$product->has_weight()) {
            return null;
        }

        return self::text(wc_format_weight($product->get_weight()));
    }
}
