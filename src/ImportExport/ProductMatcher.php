<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport;

use WP_Post;

/**
 * A product is found by SKU first, then by slug. Variations are not matched: values
 * travel with parent products only.
 */
final class ProductMatcher implements ObjectMatcher
{
    public const TYPE = 'product';

    public function type(): string
    {
        return self::TYPE;
    }

    public function find(array $object): ?int
    {
        $sku = Shape::string($object, 'sku');
        $bySku = $sku === '' ? 0 : wc_get_product_id_by_sku($sku);

        if ($bySku > 0 && get_post_type($bySku) === self::TYPE) {
            return $bySku;
        }

        $slug = Shape::string($object, 'slug');

        return $slug === '' ? null : $this->bySlug($slug);
    }

    public function identify(int $objectId): array
    {
        $sku = get_post_meta($objectId, '_sku', true);
        $post = get_post($objectId);

        return array_filter(
            [
                'type' => self::TYPE,
                'sku' => is_string($sku) ? $sku : '',
                'slug' => $post instanceof WP_Post ? $post->post_name : '',
            ],
            static fn (string $value): bool => $value !== ''
        );
    }

    public function describe(array $object): string
    {
        $sku = Shape::string($object, 'sku');

        return $sku !== '' ? sprintf('SKU %s', $sku) : urldecode(Shape::string($object, 'slug'));
    }

    /**
     * A product slug can hold characters sanitize_title() strips, such as the zero-width
     * non-joiner common in Persian. get_page_by_path() sanitizes before it looks up, so a
     * slug like that is looked up as stored when it finds nothing.
     */
    private function bySlug(string $slug): ?int
    {
        $post = get_page_by_path($slug, 'OBJECT', self::TYPE);

        if ($post instanceof WP_Post) {
            return $post->ID;
        }

        if (sanitize_title($slug) === $slug) {
            return null;
        }

        /** @var \wpdb $wpdb */
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- No WordPress API looks a post up by its slug unsanitized.
        $postId = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT ID FROM %i WHERE post_type = %s AND post_name = %s LIMIT 1',
                $wpdb->posts,
                self::TYPE,
                $slug
            )
        );

        return is_numeric($postId) ? (int) $postId : null;
    }
}
