<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Upgrade;

use Inpsyde\Modularity\Module\ExecutableModule;
use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;
use Psr\Container\ContainerInterface;

final class Module implements ServiceModule, ExecutableModule
{
    use ModuleClassNameIdTrait;

    public function services(): array
    {
        return [
            DataVersionRepository::class => static fn () => new DataVersionRepository(),
            LegacyDataProbe::class => static fn () => new LegacyDataProbe(),
            Installer::class => static fn (ContainerInterface $container) => new Installer(
                $container->get(DataVersionRepository::class),
                $container->get(LegacyDataProbe::class)
            ),
            MigrationState::class => static fn (ContainerInterface $container) => new MigrationState(
                $container->get(DataVersionRepository::class)
            ),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        add_action(
            'init',
            static function () use ($container) {
                $container->get(Installer::class)->install();
            },
            5
        );

        return true;
    }
}
