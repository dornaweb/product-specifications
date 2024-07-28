<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Integration;

use Amiut\ProductSpecs\Integration\WooCommerce\Assets;
use Amiut\ProductSpecs\Integration\WooCommerce\ProductTabs;
use Amiut\ProductSpecs\Integration\WooCommerce\WooCommerceNotInstalledNoticeHandler;
use Amiut\ProductSpecs\Repository\SpecificationsTableRepository;
use Inpsyde\Modularity\Module\ExecutableModule;
use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;
use Inpsyde\Modularity\Package;
use Psr\Container\ContainerInterface;

final class Module implements ServiceModule, ExecutableModule
{
    use ModuleClassNameIdTrait;

    public function services(): array
    {
        return [
            Assets::class => static fn (ContainerInterface $container) => new Assets(
                $container->get(Package::PROPERTIES)
            ),
            WooCommerceNotInstalledNoticeHandler::class => static fn () => new WooCommerceNotInstalledNoticeHandler(),
            ProductTabs::class => static fn (ContainerInterface $container) => new ProductTabs(
                $container->get(SpecificationsTableRepository::class)
            ),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        add_filter(
            'woocommerce_product_tabs',
            [$container->get(ProductTabs::class), 'addProductSpecificationsTab']
        );

        add_action(
            'wp_enqueue_scripts',
            [$container->get(Assets::class), 'load']
        );

        add_action(
            'admin_init',
            $container->get(WooCommerceNotInstalledNoticeHandler::class)
        );

        return true;
    }
}
