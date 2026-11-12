<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\WooCommerce;

use WC_Product;

interface ProductDataSource
{
    /**
     * Null when the product has nothing to show for this source.
     */
    public function value(WC_Product $product): mixed;

    public function plainValue(mixed $value): string;

    /**
     * Output ready. The source escapes everything it returns.
     */
    public function render(mixed $value): string;
}
