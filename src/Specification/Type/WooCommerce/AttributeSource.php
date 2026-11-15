<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\WooCommerce;

use WC_Product;

final class AttributeSource extends TextSource
{
    private const SEPARATOR = ', ';

    public function __construct(private readonly string $attribute)
    {
    }

    /**
     * WooCommerce joins a custom attribute's options with its storage delimiter, and its own
     * product page lists them separated by commas, as it does for attribute terms.
     */
    public function value(WC_Product $product): ?string
    {
        $value = TermOwner::of($product)->get_attribute($this->attribute);

        return self::text(str_replace(' ' . WC_DELIMITER . ' ', self::SEPARATOR, $value));
    }
}
