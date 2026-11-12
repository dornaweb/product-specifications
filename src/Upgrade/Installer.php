<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Upgrade;

final class Installer
{
    public function __construct(
        private readonly DataVersionRepository $repository,
        private readonly LegacyDataProbe $probe
    ) {
    }

    /**
     * Activation is not the only way in. A plugin updated through the WordPress
     * updater, WP-CLI, or by overwriting files never fires the activation hook,
     * which is the path an existing installation takes.
     */
    public function install(): void
    {
        if ($this->repository->isStored()) {
            return;
        }

        $this->repository->store(DataVersion::forInstall($this->probe->found()));
    }
}
