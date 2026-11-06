<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Settings;

use Amiut\ProductSpecs\Capability\Capabilities;
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
            SettingsSchema::class => static fn () => new SettingsSchema(),
            SettingsRepository::class => static fn (ContainerInterface $container) => new SettingsRepository(
                $container->get(SettingsSchema::class)
            ),
            Settings::class => static fn (ContainerInterface $container) => $container
                ->get(SettingsRepository::class)
                ->current(),
            SettingsRestController::class => static fn (ContainerInterface $container) => new SettingsRestController(
                $container->get(SettingsRepository::class),
                $container->get(Capabilities::class)
            ),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        add_action(
            'rest_api_init',
            [$container->get(SettingsRestController::class), 'registerRoutes']
        );

        add_action(
            'init',
            static fn () => $container->get(Settings::class),
            0
        );

        return true;
    }
}
