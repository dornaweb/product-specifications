<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Shortcode;

use Amiut\ProductSpecs\Block\CompareButton\CompareButtonBlock;
use Amiut\ProductSpecs\Block\Comparison\ComparisonBlock;
use Amiut\ProductSpecs\Block\ProductSpecifications\ProductSpecificationsBlock;
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
            SpecificationsTableShortcode::class => static fn (
                ContainerInterface $container
            ) => new SpecificationsTableShortcode(
                $container->get(ProductSpecificationsBlock::class)
            ),
            CompareButtonShortcode::class => static fn (
                ContainerInterface $container
            ) => new CompareButtonShortcode(
                $container->get(CompareButtonBlock::class)
            ),
            ComparisonShortcode::class => static fn (
                ContainerInterface $container
            ) => new ComparisonShortcode(
                $container->get(ComparisonBlock::class)
            ),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        add_action(
            'init',
            static function () use ($container) {
                add_shortcode(
                    SpecificationsTableShortcode::KEY,
                    [$container->get(SpecificationsTableShortcode::class), 'render']
                );
                add_shortcode(
                    CompareButtonShortcode::KEY,
                    [$container->get(CompareButtonShortcode::class), 'render']
                );
                add_shortcode(
                    ComparisonShortcode::KEY,
                    [$container->get(ComparisonShortcode::class), 'render']
                );
            }
        );

        return true;
    }
}
