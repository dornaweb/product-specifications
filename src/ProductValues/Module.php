<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ProductValues;

use Amiut\ProductSpecs\Assets\AssetHelper;
use Amiut\ProductSpecs\Capability\Capabilities;
use Amiut\ProductSpecs\Metabox\Metaboxes;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetRepository;
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
            ProductValueRepository::class => static fn () => new WordPressProductValueRepository(),
            ProductValueSaveService::class => static fn (
                ContainerInterface $container
            ) => new ProductValueSaveService(
                $container->get(ProductValueRepository::class),
                $container->get(SpecificationDefinitionRepository::class),
                $container->get(SpecificationTypeRegistry::class)
            ),
            ProductValuesRestController::class => static fn (
                ContainerInterface $container
            ) => new ProductValuesRestController(
                $container->get(ProductValueRepository::class),
                $container->get(ProductValueSaveService::class)
            ),
            DeletionImpactRestController::class => static fn (
                ContainerInterface $container
            ) => new DeletionImpactRestController(
                $container->get(ProductValueRepository::class),
                $container->get(SpecificationDefinitionRepository::class),
                $container->get(Capabilities::class)
            ),
            SpecificationDeletionService::class => static fn (
                ContainerInterface $container
            ) => new SpecificationDeletionService(
                $container->get(SpecificationDefinitionRepository::class),
                $container->get(SpecificationSetRepository::class),
                $container->get(ProductValueRepository::class)
            ),
            ProductValuesMetabox::class => static fn () => new ProductValuesMetabox(),
            Assets::class => static fn (ContainerInterface $container) => new Assets(
                $container->get(AssetHelper::class)
            ),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        add_action(
            Metaboxes::ACTION_SETUP,
            static fn (Metaboxes $metaboxes) => $metaboxes->add(
                $container->get(ProductValuesMetabox::class)
            )
        );

        add_action('admin_enqueue_scripts', [$container->get(Assets::class), 'load']);

        add_action(
            'rest_api_init',
            static function () use ($container): void {
                $container->get(ProductValuesRestController::class)->registerRoutes();
                $container->get(DeletionImpactRestController::class)->registerRoutes();
            }
        );

        $container->get(SpecificationDeletionService::class)->register();

        return true;
    }
}
