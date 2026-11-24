<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration\Step;

interface LegacyProducts
{
    /**
     * @return list<int> In ascending order.
     */
    public function idsAfter(int $cursor, int $limit): array;

    public function pending(): int;
}
