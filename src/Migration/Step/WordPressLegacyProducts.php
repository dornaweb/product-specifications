<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration\Step;

use Amiut\ProductSpecs\ProductValues\WordPressProductValueRepository;
use RuntimeException;
use wpdb;

final class WordPressLegacyProducts implements LegacyProducts
{
    public function idsAfter(int $cursor, int $limit): array
    {
        $database = $this->database();
        $ids = $database->get_col(
            (string) $database->prepare(
                "SELECT DISTINCT meta.post_id
                FROM %i meta
                INNER JOIN %i post ON post.ID = meta.post_id
                WHERE post.post_type = 'product'
                    AND meta.meta_key IN (%s, %s)
                    AND meta.post_id > %d
                ORDER BY meta.post_id ASC
                LIMIT %d",
                $database->postmeta,
                $database->posts,
                ProductValuesStep::SNAPSHOT_META,
                ProductValuesStep::TABLE_META,
                $cursor,
                $limit
            )
        );

        $this->failOnError($database);

        $productIds = [];

        foreach ($ids as $id) {
            if (is_numeric($id)) {
                $productIds[] = (int) $id;
            }
        }

        return $productIds;
    }

    public function pending(): int
    {
        $database = $this->database();
        $count = $database->get_var(
            (string) $database->prepare(
                "SELECT COUNT(DISTINCT meta.post_id)
                FROM %i meta
                INNER JOIN %i post ON post.ID = meta.post_id
                WHERE post.post_type = 'product'
                    AND meta.meta_key IN (%s, %s)
                    AND NOT EXISTS (
                        SELECT 1 FROM %i current
                        WHERE current.post_id = meta.post_id
                            AND current.meta_key IN (%s, %s, %s)
                    )",
                $database->postmeta,
                $database->posts,
                ProductValuesStep::SNAPSHOT_META,
                ProductValuesStep::TABLE_META,
                $database->postmeta,
                ProductValuesStep::MIGRATED_META,
                WordPressProductValueRepository::VALUES_META_KEY,
                WordPressProductValueRepository::SET_META_KEY
            )
        );

        $this->failOnError($database);

        return is_numeric($count) ? (int) $count : 0;
    }

    // A failed query returns no rows, which would read as a catalog with nothing left
    // to migrate and end the run early.
    private function failOnError(wpdb $database): void
    {
        if ($database->last_error !== '') {
            throw new RuntimeException($database->last_error);
        }
    }

    private function database(): wpdb
    {
        global $wpdb;

        if (!$wpdb instanceof wpdb) {
            throw new RuntimeException(__('The database is not available.', 'product-specifications'));
        }

        return $wpdb;
    }
}
