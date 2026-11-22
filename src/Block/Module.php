<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Block;

use Amiut\ProductSpecs\Block\CompareButton\CompareButtonBlock;
use Amiut\ProductSpecs\Block\Comparison\ComparisonBlock;
use Amiut\ProductSpecs\Comparison\CompareButton;
use Amiut\ProductSpecs\Block\ProductSpecifications\ProductSpecificationsBlock;
use Amiut\ProductSpecs\Comparison\ComparisonReader;
use Amiut\ProductSpecs\Settings\SettingsRepository;
use Amiut\ProductSpecs\Storefront\ProductSpecifications;
use Amiut\ProductSpecs\Settings\SettingsProviders;
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
            BlockRegistry::class => static function (ContainerInterface $container): BlockRegistry {
                /** @var PluginProperties $properties */
                $properties = $container->get(Package::PROPERTIES);

                return new BlockRegistry(
                    $properties->basePath() . 'assets/Block',
                    $container->get(SettingsRepository::class)
                );
            },
            ProductSpecificationsBlock::class => static fn (ContainerInterface $container) => new ProductSpecificationsBlock(
                $container->get(ProductSpecifications::class)
            ),
            CompareButtonBlock::class => static fn (ContainerInterface $container) => new CompareButtonBlock(
                $container->get(CompareButton::class)
            ),
            ComparisonBlock::class => static fn (ContainerInterface $container) => new ComparisonBlock(
                $container->get(ComparisonReader::class),
                $container->get(SettingsRepository::class)
            ),
        ];
    }

    public function extensions(): array
    {
        return [
            SettingsProviders::class => SettingsProviders::adding(new StylesheetSettings()),
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
