<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration\Step;

use Amiut\ProductSpecs\Migration\ChunkResult;
use Amiut\ProductSpecs\Migration\IdBatch;
use Amiut\ProductSpecs\Migration\MigrationStep;
use Amiut\ProductSpecs\ProductValues\ProductValueDocument;
use Amiut\ProductSpecs\ProductValues\ProductValueRepository;
use Amiut\ProductSpecs\ProductValues\WordPressProductValueRepository;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetPostType;
use WP_Post;

final class ProductValuesStep implements MigrationStep
{
    public const KEY = 'values';

    private const SNAPSHOT_META = '_dwps_specification_table';
    private const TABLE_META = '_dwps_table';
    private const MIGRATED_META = '_product_specifications_migrated';

    public function __construct(
        private readonly SnapshotValueReader $reader,
        private readonly ProductValueRepository $values
    ) {
    }

    public function key(): string
    {
        return self::KEY;
    }

    public function total(): int
    {
        return count($this->productIds([
            'relation' => 'AND',
            ['key' => self::MIGRATED_META, 'compare' => 'NOT EXISTS'],
            ['key' => WordPressProductValueRepository::VALUES_META_KEY, 'compare' => 'NOT EXISTS'],
            ['key' => WordPressProductValueRepository::SET_META_KEY, 'compare' => 'NOT EXISTS'],
        ]));
    }

    public function run(int $cursor, int $limit): ChunkResult
    {
        $batch = IdBatch::after($this->productIds(), $cursor, $limit);
        $processed = 0;
        $skipped = [];
        $errors = [];

        foreach ($batch->ids() as $productId) {
            if ($this->wasMigrated($productId)) {
                continue;
            }

            if ($this->holdsCurrentData($productId)) {
                $skipped[] = $productId;
                continue;
            }

            $processed++;

            $error = $this->migrate($productId);

            if ($error !== null) {
                $errors[$productId] = $error;
            }
        }

        return $batch->result($processed, $skipped, $errors);
    }

    private function migrate(int $productId): ?string
    {
        $read = $this->reader->read(get_post_meta($productId, self::SNAPSHOT_META, true));
        $setSlug = $this->setSlug($productId);

        // Values go first: a product holding a set is skipped as already migrated, so a
        // set written ahead of values that then failed would hide them from every re-run.
        $stored = $read['values'] === []
            || $this->values->replace($productId, ProductValueDocument::fromValues($read['values']));

        if ($stored && $setSlug !== null) {
            $stored = $this->values->assignSet($productId, $setSlug);
        }

        if (!$stored) {
            return __('The product specifications could not be saved.', 'product-specifications');
        }

        update_post_meta($productId, self::MIGRATED_META, 1);

        if ($read['unresolved'] !== []) {
            return sprintf(
                /* translators: %s: comma-separated names of specifications. */
                __('These specifications could not be migrated: %s', 'product-specifications'),
                implode(', ', $read['unresolved'])
            );
        }

        return null;
    }

    // A chunk that stops part-way runs again from its start. The marker tells the
    // products it already migrated apart from products that held data beforehand.
    private function wasMigrated(int $productId): bool
    {
        return metadata_exists('post', $productId, self::MIGRATED_META);
    }

    private function holdsCurrentData(int $productId): bool
    {
        return metadata_exists('post', $productId, WordPressProductValueRepository::VALUES_META_KEY)
            || metadata_exists('post', $productId, WordPressProductValueRepository::SET_META_KEY);
    }

    private function setSlug(int $productId): ?string
    {
        $tableId = get_post_meta($productId, self::TABLE_META, true);
        $table = is_numeric($tableId) && (int) $tableId > 0 ? get_post((int) $tableId) : null;

        if (!$table instanceof WP_Post || $table->post_type !== SpecificationSetPostType::KEY) {
            return null;
        }

        $slug = $table->post_status === 'trash'
            ? get_post_meta($table->ID, '_wp_desired_post_slug', true)
            : $table->post_name;

        return is_string($slug) && $slug !== '' ? $slug : null;
    }

    /**
     * @param array<array-key, mixed> $pendingOnly
     * @return list<int>
     */
    private function productIds(array $pendingOnly = []): array
    {
        $holdingLegacyData = [
            'relation' => 'OR',
            ['key' => self::SNAPSHOT_META, 'compare' => 'EXISTS'],
            ['key' => self::TABLE_META, 'compare' => 'EXISTS'],
        ];

        $ids = get_posts([
            'post_type' => 'product',
            'post_status' => array_values(get_post_stati()),
            'posts_per_page' => -1,
            'fields' => 'ids',
            'no_found_rows' => true,
            'meta_query' => $pendingOnly === []
                ? $holdingLegacyData
                : array_merge($pendingOnly, [$holdingLegacyData]),
        ]);

        $productIds = [];

        foreach (is_array($ids) ? $ids : [] as $id) {
            if (is_numeric($id) && (int) $id > 0) {
                $productIds[] = (int) $id;
            }
        }

        return $productIds;
    }
}
