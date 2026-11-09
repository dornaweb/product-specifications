<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\WooCommerce;

use WC_Product;

final class DimensionsSource extends TextSource
{
    public function value(WC_Product $product): ?string
    {
        if (!$product->has_dimensions()) {
            return null;
        }

        $dimensions = [$product->get_length(), $product->get_width(), $product->get_height()];

        return self::text(wc_format_dimensions($dimensions));
    }
}
