<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ProductValues;

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

    public function countContaining(string $specificationKey): int
    {
        $count = 0;

        foreach ($this->postIdsWithValues() as $postId) {
            if ($this->read($postId)->has($specificationKey)) {
                ++$count;
            }
        }

        return $count;
    }

    public function countUsing(string $specificationKey, array $values): int
    {
        $count = 0;

        foreach ($this->postIdsWithValues() as $postId) {
            $stored = $this->read($postId)->values()[$specificationKey] ?? null;

            if (is_scalar($stored) && in_array((string) $stored, $values, true)) {
                ++$count;
            }
        }

        return $count;
    }

    public function purge(string $specificationKey): bool
    {
        foreach ($this->postIdsWithValues() as $postId) {
            $document = $this->read($postId);

            if (!$document->has($specificationKey)) {
                continue;
            }

            if (!$this->replace($postId, $document->without($specificationKey))) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<int>
     */
    private function postIdsWithValues(): array
    {
        $ids = get_posts(
            [
                'post_type' => ['product', 'product_variation'],
                'post_status' => 'any',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'meta_key' => self::VALUES_META_KEY,
                'no_found_rows' => true,
            ]
        );

        return array_values(array_map('intval', is_array($ids) ? $ids : []));
    }
}
