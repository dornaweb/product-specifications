<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\WooCommerce;

use WC_Product;

enum Dimension: string
{
    case Length = 'length';
    case Width = 'width';
    case Height = 'height';

    public function of(WC_Product $product): string
    {
        return (string) match ($this) {
            self::Length => $product->get_length(),
            self::Width => $product->get_width(),
            self::Height => $product->get_height(),
        };
    }
}
