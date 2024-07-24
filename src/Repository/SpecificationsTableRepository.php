<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Repository;

class SpecificationsTableRepository
{
    private const SPECIFICATIONS_TABLE_META_KEY = '_dwps_specification_table';

    public function findByProductId(int $productId): array
    {
        $specificationsTable = get_post_meta($productId, self::SPECIFICATIONS_TABLE_META_KEY, true);

        return is_array($specificationsTable) ? $specificationsTable : [];
    }

    public function productHasSpecsTable(int $productId): bool
    {
        $tableId = get_post_meta($productId, '_dwps_table', true);
        $table = get_post_meta($productId, '_dwps_specification_table', true);

        return !empty($tableId) && !empty($table);
    }
}
