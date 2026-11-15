<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\WooCommerce;

use WC_Product;

final class SkuSource extends TextSource
{
    public function value(WC_Product $product): ?string
    {
        return self::text($product->get_sku());
    }
}
