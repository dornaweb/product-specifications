<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Integration;

use Amiut\ProductSpecs\Integration\WooCommerce\FeaturesCompatibilityDeclarations;
use Amiut\ProductSpecs\Integration\WooCommerce\ProductTabs;
use Amiut\ProductSpecs\Integration\WooCommerce\WooCommerceNotInstalledNoticeHandler;
use Amiut\ProductSpecs\Settings\SettingsRepository;
use Amiut\ProductSpecs\Block\ProductSpecifications\ProductSpecificationsBlock;
use Amiut\ProductSpecs\Migration\MigrationState;
use Amiut\ProductSpecs\Settings\SettingsProviders;
use Amiut\ProductSpecs\Integration\WooCommerce\ProductTabSettings;
use Inpsyde\Modularity\Module\ExecutableModule;
use Inpsyde\Modularity\Module\ExtendingModule;
use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;
use Inpsyde\Modularity\Package;
use Inpsyde\Modularity\Properties\PluginProperties;
use Psr\Container\ContainerInterface;

final class Module implements ServiceModule, ExtendingModule, ExecutableModule
{
    use ModuleClassNameIdTrait;

    public function services(): array
    {
        return [
            WooCommerceNotInstalledNoticeHandler::class => static fn () =>
                new WooCommerceNotInstalledNoticeHandler(),
            ProductTabs::class => static fn (ContainerInterface $container) => new ProductTabs(
                $container->get(ProductSpecificationsBlock::class),
                $container->get(SettingsRepository::class),
                $container->get(MigrationState::class)
            ),
            FeaturesCompatibilityDeclarations::class => static function (
                ContainerInterface $container
            ): FeaturesCompatibilityDeclarations {
                /** @var PluginProperties $properties */
                $properties = $container->get(Package::PROPERTIES);

                return new FeaturesCompatibilityDeclarations($properties);
            },
        ];
    }

    public function extensions(): array
    {
        return [
            SettingsProviders::class => SettingsProviders::adding(new ProductTabSettings()),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        add_action(
            'before_woocommerce_init',
            $container->get(FeaturesCompatibilityDeclarations::class)
        );

        add_filter(
            'woocommerce_product_tabs',
            [$container->get(ProductTabs::class), 'addProductSpecificationsTab']
        );

        add_action(
            'admin_init',
            $container->get(WooCommerceNotInstalledNoticeHandler::class)
        );

        return true;
    }
}
