<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Repository;

class SpecificationsTableRepository
{
    private const SPECIFICATIONS_TABLE_META_KEY = '_dwps_specification_table'; // TODO: Move to its own class.

    public function findByProductId(int $productId): array
    {
        $specificationsTable = get_post_meta($productId, self::SPECIFICATIONS_TABLE_META_KEY, true);

        return is_array($specificationsTable) ? $specificationsTable : [];
    }
}
