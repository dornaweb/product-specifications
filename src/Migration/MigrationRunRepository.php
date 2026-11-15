<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration;

final class MigrationRunRepository
{
    public const OPTION = 'product_specifications_migration_run';

    public function stored(): MigrationRun
    {
        return MigrationRun::fromStorage(get_option(self::OPTION, null));
    }

    public function store(MigrationRun $run): void
    {
        update_option(self::OPTION, $run->toStorage(), false);
    }
}
