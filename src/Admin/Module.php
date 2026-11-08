<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Admin;

use Amiut\ProductSpecs\Assets\AssetHelper;
use Amiut\ProductSpecs\Capability\Capabilities;
use Amiut\ProductSpecs\Migration\MigrationNotice;
use Amiut\ProductSpecs\ImportExport\ExportDownload;
use Amiut\ProductSpecs\Migration\MigrationState;
use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;
use Psr\Container\ContainerInterface;
use Inpsyde\Modularity\Module\ExecutableModule;

final class Module implements ServiceModule, ExecutableModule
{
    use ModuleClassNameIdTrait;

    public function services(): array
    {
        return [
            AdminScreens::class => static fn (ContainerInterface $container) => new AdminScreens(
                $container->get(Capabilities::class),
                $container->get(MigrationState::class)
            ),
            Assets::class => static fn (ContainerInterface $container) => new Assets(
                $container->get(AssetHelper::class),
                $container->get(AdminScreens::class),
                $container->get(MigrationState::class),
                $container->get(MigrationNotice::class),
                $container->get(ExportDownload::class)
            ),
            PageRegistry::class => static fn () => new WooCommerceAdminPageRegistry(),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        // Ahead of WooCommerce's admin app (15), so the routes exist when the app first
        // renders. A route added later renders WooCommerce's "Not allowed" page meanwhile.
        add_action(
            'admin_enqueue_scripts',
            [$container->get(Assets::class), 'load'],
            10
        );

        add_action(
            'admin_menu',
            function () use ($container) {
                $this->registerMenuPages($container);
            }
        );

        return true;
    }

    private function registerMenuPages(ContainerInterface $container): void
    {
        $registry = $container->get(PageRegistry::class);

        foreach ($container->get(AdminScreens::class)->pages() as $page) {
            $registry->register($page);
        }
    }
}
