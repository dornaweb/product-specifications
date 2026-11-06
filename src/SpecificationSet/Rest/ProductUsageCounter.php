<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet\Rest;

use Amiut\ProductSpecs\ProductValues\WordPressProductValueRepository;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetPostType;
use wpdb;

/**
 * How many products are assigned to each set, counted once for a whole page.
 *
 * The assignment is a single indexed meta value, so one grouped query answers
 * every row on the screen. The products holding a value for one *specification*
 * are a different question with a different cost, and are not counted anywhere.
 */
final class ProductUsageCounter
{
    /**
     * @var array<int, int>|null
     */
    private ?array $counts = null;

    public function forSet(int $setId): int
    {
        return $this->counts()[$setId] ?? 0;
    }

    /**
     * @return array<int, int>
     */
    private function counts(): array
    {
        if (is_array($this->counts)) {
            return $this->counts;
        }

        /** @var wpdb $db */
        $db = $GLOBALS['wpdb'];

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
        // Table names go through %i so the query stays a literal string, which
        // is the only shape prepare() can guarantee is safe.
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $db->get_results(
            $db->prepare(
                'SELECT sets.ID AS set_id, COUNT(DISTINCT products.ID) AS total
                 FROM %i AS meta
                 INNER JOIN %i AS products ON products.ID = meta.post_id
                 INNER JOIN %i AS sets
                    ON sets.post_name = meta.meta_value
                   AND sets.post_type = %s
                 WHERE meta.meta_key = %s
                   AND products.post_type = %s
                   AND products.post_status != %s
                 GROUP BY sets.ID',
                $db->postmeta,
                $db->posts,
                $db->posts,
                SpecificationSetPostType::KEY,
                WordPressProductValueRepository::SET_META_KEY,
                'product',
                'trash'
            ),
            'ARRAY_A'
        ) ?? [];
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery

        $counts = [];

        foreach ($rows as $row) {
            $setId = is_numeric($row['set_id'] ?? null) ? (int) $row['set_id'] : 0;

            if ($setId > 0) {
                $counts[$setId] = is_numeric($row['total'] ?? null) ? (int) $row['total'] : 0;
            }
        }

        $this->counts = $counts;

        return $counts;
    }
}
