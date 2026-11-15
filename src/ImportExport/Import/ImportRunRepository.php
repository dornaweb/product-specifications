<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport\Import;

final class ImportRunRepository
{
    public const OPTION = 'product_specifications_import_run';

    public function stored(): ?ImportRun
    {
        return ImportRun::fromStorage(get_option(self::OPTION, null));
    }

    public function store(ImportRun $run): void
    {
        update_option(self::OPTION, $run->toStorage(), false);
    }

    public function delete(): void
    {
        delete_option(self::OPTION);
    }
}
