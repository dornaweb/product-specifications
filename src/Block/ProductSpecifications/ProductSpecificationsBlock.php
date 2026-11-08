<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Block\ProductSpecifications;

use Amiut\ProductSpecs\Storefront\SpecificationsTable;
use Amiut\ProductSpecs\Storefront\ProductSpecifications;
use WC_Product;

final class ProductSpecificationsBlock
{
    public const NAME = 'product-specifications/product-specifications';

    public function __construct(private readonly ProductSpecifications $specifications)
    {
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function table(array $attributes): SpecificationsTable
    {
        $product = $this->product($attributes['productId'] ?? null);

        return $product === null ? SpecificationsTable::empty() : $this->specifications->table($product);
    }

    public function hasTableFor(WC_Product $product): bool
    {
        return $this->specifications->has($product);
    }

    public function renderFor(int|WC_Product $product): string
    {
        return render_block([
            'blockName' => self::NAME,
            'attrs' => ['productId' => $product instanceof WC_Product ? $product->get_id() : $product],
            'innerBlocks' => [],
            'innerHTML' => '',
            'innerContent' => [],
        ]);
    }

    private function product(mixed $productId): ?WC_Product
    {
        $id = is_numeric($productId) ? (int) $productId : 0;
        $product = wc_get_product($id > 0 ? $id : false);

        return $product instanceof WC_Product ? $product : null;
    }
}
