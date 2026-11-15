<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Upgrade;

final class DataVersionRepository
{
    public const OPTION = 'product_specifications_data_version';

    public function isStored(): bool
    {
        return get_option(self::OPTION, null) !== null;
    }

    public function stored(): DataVersion
    {
        return DataVersion::fromStored(get_option(self::OPTION, null));
    }

    public function store(DataVersion $version): void
    {
        update_option(self::OPTION, $version->value(), true);
    }
}
