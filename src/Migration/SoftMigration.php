<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration;

/**
 * Moves data that is safe to convert without asking the merchant, outside the
 * migration run. It runs on every request until the site is migrated, so it must
 * find for itself whether there is anything to move, and never overwrite data
 * already in the current shape.
 */
interface SoftMigration
{
    public function migrate(): void;
}
