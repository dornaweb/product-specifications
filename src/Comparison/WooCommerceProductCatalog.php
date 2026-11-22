<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Comparison;

use Amiut\ProductSpecs\ProductValues\WordPressProductValueRepository;
use WC_Product;
use WP_Post;
use WP_Query;

final class WooCommerceProductCatalog implements ProductCatalog
{
    public function comparableProducts(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return $this->products(new WP_Query([
            'post_type' => 'product',
            'post_status' => 'publish',
            'has_password' => false,
            'post__in' => $ids,
            'orderby' => 'post__in',
            'posts_per_page' => count($ids),
            'ignore_sticky_posts' => true,
            'no_found_rows' => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => true,
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- WooCommerce's own visibility terms.
            'tax_query' => $this->comparableVisibility(),
        ]));
    }

    public function searchInSet(string $setSlug, string $search, array $exclude, int $limit): array
    {
        return $this->products(new WP_Query([
            'post_type' => 'product',
            'post_status' => 'publish',
            'has_password' => false,
            's' => $search,
            // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- At most a comparison's ids.
            'post__not_in' => $exclude,
            'posts_per_page' => $limit,
            'ignore_sticky_posts' => true,
            'no_found_rows' => true,
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- The set is only known by this meta.
            'meta_query' => [
                ['key' => WordPressProductValueRepository::SET_META_KEY, 'value' => $setSlug],
            ],
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- WooCommerce's own visibility terms.
            'tax_query' => $this->comparableVisibility(),
        ]));
    }

    public function isComparable(WC_Product $product): bool
    {
        return $product->get_status() === 'publish'
            && $product->get_post_password() === ''
            && $product->get_catalog_visibility() !== 'hidden'
            && ($product->is_in_stock() || !$this->hidesOutOfStock());
    }

    /**
     * A hidden product carries both exclusion terms; either one alone still leaves it
     * comparable.
     *
     * @return array<int|string, mixed>
     */
    private function comparableVisibility(): array
    {
        $terms = wc_get_product_visibility_term_ids();
        $visibility = [
            'relation' => 'AND',
            [
                'relation' => 'OR',
                $this->without((int) ($terms['exclude-from-catalog'] ?? 0)),
                $this->without((int) ($terms['exclude-from-search'] ?? 0)),
            ],
        ];

        if ($this->hidesOutOfStock()) {
            $visibility[] = $this->without((int) ($terms['outofstock'] ?? 0));
        }

        return $visibility;
    }

    /**
     * @return array<string, mixed>
     */
    private function without(int $termTaxonomyId): array
    {
        return [
            'taxonomy' => 'product_visibility',
            'field' => 'term_taxonomy_id',
            'terms' => [$termTaxonomyId],
            'operator' => 'NOT IN',
        ];
    }

    private function hidesOutOfStock(): bool
    {
        return get_option('woocommerce_hide_out_of_stock_items') === 'yes';
    }

    /**
     * @return list<WC_Product>
     */
    private function products(WP_Query $query): array
    {
        $products = [];

        foreach ($query->posts as $post) {
            $product = $post instanceof WP_Post ? wc_get_product($post) : null;

            if ($product instanceof WC_Product) {
                $products[] = $product;
            }
        }

        return $products;
    }
}
