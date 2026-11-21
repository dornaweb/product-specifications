<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Comparison;

use WC_Product;

/**
 * A product is comparable when it is published, has no password, is not hidden from the
 * catalog, and is in stock if the store hides out-of-stock products.
 */
interface ProductCatalog
{
    /**
     * @param list<int> $ids
     * @return list<WC_Product> In the order of the ids.
     */
    public function comparableProducts(array $ids): array;

    /**
     * @param list<int> $exclude
     * @return list<WC_Product>
     */
    public function searchInSet(string $setSlug, string $search, array $exclude, int $limit): array;

    public function isComparable(WC_Product $product): bool;
}
