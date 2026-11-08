<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport;

use Amiut\ProductSpecs\Upgrade\MigrationState;
use RuntimeException;

/**
 * Packages are read and written in the migrated data model, so nothing moves in or out of a
 * store that still has data to migrate.
 */
final class MigrationRequired extends RuntimeException
{
    public static function unlessMigrated(MigrationState $state): void
    {
        if ($state->requiresMigration()) {
            throw new self(
                __('Import and export are available once the migration is complete.', 'product-specifications')
            );
        }
    }
}
