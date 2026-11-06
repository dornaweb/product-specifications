<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Upgrade;

final class MigrationState
{
    private ?DataVersion $version = null;

    public function __construct(private readonly DataVersionRepository $repository)
    {
    }

    public function isMigrated(): bool
    {
        return $this->dataVersion()->isMigrated();
    }

    public function requiresMigration(): bool
    {
        return !$this->isMigrated();
    }

    public function dataVersion(): DataVersion
    {
        return $this->version ??= $this->repository->stored();
    }
}
