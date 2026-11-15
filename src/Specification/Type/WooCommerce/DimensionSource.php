<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\WooCommerce;

use WC_Product;

final class DimensionSource extends TextSource
{
    public function __construct(private readonly Dimension $dimension)
    {
    }

    /**
     * Empty exactly when WooCommerce's own dimensions check would skip it: on a virtual
     * product, and for a value PHP reads as false, which includes "0".
     */
    public function value(WC_Product $product): ?string
    {
        $value = $this->dimension->of($product);

        if ($product->is_virtual() || $value === '' || $value === '0') {
            return null;
        }

        return self::text(wc_format_dimensions([$value]));
    }
}
