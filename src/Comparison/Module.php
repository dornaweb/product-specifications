<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Comparison;

use Amiut\ProductSpecs\Comparison\Rest\ComparableProductsRestController;
use Amiut\ProductSpecs\Migration\MigrationState;
use Amiut\ProductSpecs\ProductValues\ProductValueRepository;
use Amiut\ProductSpecs\Settings\SettingsProviders;
use Amiut\ProductSpecs\Settings\SettingsRepository;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetRepository;
use Amiut\ProductSpecs\Storefront\ValueRenderer;
use Inpsyde\Modularity\Module\ExecutableModule;
use Inpsyde\Modularity\Module\ExtendingModule;
use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;
use Psr\Container\ContainerInterface;

final class Module implements ServiceModule, ExtendingModule, ExecutableModule
{
    use ModuleClassNameIdTrait;

    public function services(): array
    {
        return [
            ProductCatalog::class => static fn () => new WooCommerceProductCatalog(),
            ComparisonReader::class => static fn (ContainerInterface $container) => new ComparisonReader(
                $container->get(ProductCatalog::class),
                $container->get(ProductValueRepository::class),
                $container->get(SpecificationSetRepository::class),
                $container->get(SpecificationDefinitionRepository::class),
                $container->get(ValueRenderer::class),
                $container->get(MigrationState::class)
            ),
            CompareButton::class => static fn (ContainerInterface $container) => new CompareButton(
                $container->get(SettingsRepository::class),
                $container->get(ProductValueRepository::class),
                $container->get(SpecificationSetRepository::class),
                $container->get(ProductCatalog::class),
                $container->get(MigrationState::class)
            ),
            ComparePageInstaller::class => static fn (ContainerInterface $container) => new ComparePageInstaller(
                $container->get(SettingsRepository::class)
            ),
            CompareRobots::class => static fn (ContainerInterface $container) => new CompareRobots(
                $container->get(SettingsRepository::class)
            ),
            ComparableProductsRestController::class => static fn (
                ContainerInterface $container
            ) => new ComparableProductsRestController(
                $container->get(ProductCatalog::class),
                $container->get(SpecificationSetRepository::class)
            ),
        ];
    }

    public function extensions(): array
    {
        return [
            SettingsProviders::class => SettingsProviders::adding(new ComparisonSettings()),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        ProductHeader::register();

        add_action(
            'init',
            static function () use ($container): void {
                $container->get(ComparePageInstaller::class)->install();
            },
            20
        );

        add_filter('wp_robots', [$container->get(CompareRobots::class), 'addNoindex']);
        add_filter(
            'wp_sitemaps_posts_query_args',
            [$container->get(CompareRobots::class), 'excludeFromSitemap'],
            10,
            2
        );

        add_action(
            'rest_api_init',
            [$container->get(ComparableProductsRestController::class), 'registerRoutes']
        );

        return true;
    }
}
