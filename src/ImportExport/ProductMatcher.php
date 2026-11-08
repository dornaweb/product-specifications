<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport;

use Amiut\ProductSpecs\Slug\SlugLookup;
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

    private function bySlug(string $slug): ?int
    {
        return SlugLookup::post($slug, self::TYPE)?->ID;
    }
}
