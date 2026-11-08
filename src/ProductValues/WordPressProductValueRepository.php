<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ProductValues;

use wpdb;

final class WordPressProductValueRepository implements ProductValueRepository
{
    public const VALUES_META_KEY = 'product_specifications_values';
    public const SET_META_KEY = 'product_specifications_set';

    public function read(int $postId): ProductValueDocument
    {
        return ProductValueDocument::fromStorage(
            get_post_meta($postId, self::VALUES_META_KEY, true)
        );
    }

    public function replace(int $postId, ProductValueDocument $document): bool
    {
        if ($document->values() === []) {
            return $this->delete($postId);
        }

        if ($this->read($postId)->toStorage() === $document->toStorage()) {
            return true;
        }

        return update_post_meta($postId, self::VALUES_META_KEY, $document->toStorage()) !== false;
    }

    public function delete(int $postId): bool
    {
        if (!metadata_exists('post', $postId, self::VALUES_META_KEY)) {
            return true;
        }

        return delete_post_meta($postId, self::VALUES_META_KEY);
    }

    public function assignedSet(int $postId): ?string
    {
        $stored = get_post_meta($postId, self::SET_META_KEY, true);
        $slug = is_scalar($stored) ? trim((string) $stored) : '';

        return $slug === '' ? null : $slug;
    }

    public function assignSet(int $postId, ?string $setSlug): bool
    {
        if ($setSlug === null || $setSlug === '') {
            if (!metadata_exists('post', $postId, self::SET_META_KEY)) {
                return true;
            }

            return delete_post_meta($postId, self::SET_META_KEY);
        }

        if ($this->assignedSet($postId) === $setSlug) {
            return true;
        }

        return update_post_meta($postId, self::SET_META_KEY, $setSlug) !== false;
    }

    /**
     * The query narrows the products to those whose stored map contains the key's
     * serialized form, which a value can also produce, so each match is confirmed
     * against the decoded map.
     */
    public function countUsing(string $specificationKey, array $values): int
    {
        $count = 0;

        foreach ($this->storedMapsMentioning($specificationKey) as $stored) {
            $document = ProductValueDocument::fromStorage(maybe_unserialize($stored));
            $value = $document->values()[$specificationKey] ?? null;

            if (is_scalar($value) && in_array((string) $value, $values, true)) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * @return list<string>
     */
    private function storedMapsMentioning(string $specificationKey): array
    {
        /** @var wpdb $db */
        $db = $GLOBALS['wpdb'];
        $serializedKey = sprintf('s:%d:"%s";', strlen($specificationKey), $specificationKey);

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
        $stored = $db->get_col(
            $db->prepare(
                'SELECT meta.meta_value
                 FROM %i AS meta
                 INNER JOIN %i AS posts ON posts.ID = meta.post_id
                 WHERE meta.meta_key = %s
                   AND meta.meta_value LIKE %s
                   AND posts.post_type IN (%s, %s)
                   AND posts.post_status NOT IN (%s, %s)',
                $db->postmeta,
                $db->posts,
                self::VALUES_META_KEY,
                '%' . $db->esc_like($serializedKey) . '%',
                'product',
                'product_variation',
                'trash',
                'auto-draft'
            )
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery

        return array_values(array_filter($stored, 'is_string'));
    }
}
