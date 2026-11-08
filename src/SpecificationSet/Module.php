<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet;

use Amiut\ProductSpecs\Capability\Capabilities;
use Amiut\ProductSpecs\SpecificationSet\Rest\ProductUsageCounter;
use Amiut\ProductSpecs\SpecificationSet\Rest\SpecificationSetRestField;
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
            SpecificationSetPostType::class => static fn (
                ContainerInterface $container
            ) => new SpecificationSetPostType(
                $container->get(Capabilities::class)
            ),
            StructureMetaDefinition::class => static fn () => new StructureMetaDefinition(),
            StructureSanitizer::class => static fn () => new StructureSanitizer(),
            StructureMetaRegistrar::class => static fn (ContainerInterface $container) => new StructureMetaRegistrar(
                $container->get(StructureMetaDefinition::class),
                $container->get(StructureSanitizer::class),
                $container->get(Capabilities::class)
            ),
            SpecificationSetMapper::class => static fn () => new SpecificationSetMapper(),
            SpecificationSetRepository::class => static fn (ContainerInterface $container) => new SpecificationSetRepositoryImpl(
                $container->get(SpecificationSetMapper::class)
            ),
            ProductUsageCounter::class => static fn () => new ProductUsageCounter(),
            DraftSlug::class => static fn () => new DraftSlug(),
            SpecificationSetRestField::class => static fn (
                ContainerInterface $container
            ) => new SpecificationSetRestField(
                $container->get(ProductUsageCounter::class)
            ),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        add_action(
            'init',
            static function () use ($container) {
                $container->get(SpecificationSetPostType::class)->init();
            },
        );

        $container->get(StructureMetaRegistrar::class)->init();

        add_filter('wp_insert_post_data', [$container->get(DraftSlug::class), 'assign'], 10, 2);

        add_action(
            'rest_api_init',
            static function () use ($container) {
                $container->get(SpecificationSetRestField::class)->register();
            }
        );

        return true;
    }
}
