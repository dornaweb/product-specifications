<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Suggestion;

/**
 * Kept out of term meta, which the storefront loads whole for every specification it
 * shows, and never autoloaded.
 */
final class ValueUsageRepository
{
    public const OPTION_PREFIX = 'product_specifications_value_usage_';

    public function find(int $termId): ?ValueUsage
    {
        $stored = get_option(self::OPTION_PREFIX . $termId);

        return $stored === false ? null : ValueUsage::fromStorage($stored);
    }

    public function save(int $termId, ValueUsage $usage): void
    {
        update_option(self::OPTION_PREFIX . $termId, $usage->toStorage(), false);
    }

    public function delete(int $termId): void
    {
        delete_option(self::OPTION_PREFIX . $termId);
    }
}
