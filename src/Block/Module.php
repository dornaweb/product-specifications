<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Block;

use Amiut\ProductSpecs\Block\ProductSpecifications\ProductSpecificationsBlock;
use Amiut\ProductSpecs\Settings\SettingsRepository;
use Amiut\ProductSpecs\Storefront\SpecificationsTableReader;
use Inpsyde\Modularity\Module\ExecutableModule;
use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;
use Inpsyde\Modularity\Package;
use Inpsyde\Modularity\Properties\PluginProperties;
use Psr\Container\ContainerInterface;

final class Module implements ServiceModule, ExecutableModule
{
    use ModuleClassNameIdTrait;

    public function services(): array
    {
        return [
            BlockRegistry::class => static function (ContainerInterface $container): BlockRegistry {
                /** @var PluginProperties $properties */
                $properties = $container->get(Package::PROPERTIES);

                return new BlockRegistry(
                    $properties->basePath() . 'assets/Block',
                    $container->get(SettingsRepository::class)
                );
            },
            ProductSpecificationsBlock::class => static fn (ContainerInterface $container) => new ProductSpecificationsBlock(
                $container->get(SpecificationsTableReader::class)
            ),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        add_action(
            'init',
            static function () use ($container): void {
                $container->get(BlockRegistry::class)->register();
            }
        );

        return true;
    }
}
