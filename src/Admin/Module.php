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

    public const PAGE_ID = 'product-specifications';
    public const ROOT_PATH = '/product-specifications';

    public function services(): array
    {
        return [
            Assets::class => static fn (ContainerInterface $container) => new Assets(
                $container->get(AssetHelper::class),
                $container->get(Capabilities::class),
                $container->get(MigrationState::class),
                $container->get(MigrationNotice::class),
                $container->get(ExportDownload::class)
            ),
            PageRegistry::class => static fn () => new WooCommerceAdminPageRegistry(),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        add_action(
            'admin_enqueue_scripts',
            [$container->get(Assets::class), 'load'],
            30
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
        $capability = $container->get(Capabilities::class)->manageSpecifications();

        $container->get(PageRegistry::class)->register(
            new Page(
                self::PAGE_ID,
                __('Product Specifications', 'product-specifications'),
                self::ROOT_PATH,
                'woocommerce',
                $capability,
                50
            )
        );
    }
}
