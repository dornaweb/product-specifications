<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Shortcode;

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
            SpecificationsTable::class => static fn (ContainerInterface $container) => new SpecificationsTable(
                $container->get(ProductSpecificationsBlock::class)
            ),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        $tableShortcode = $container->get(SpecificationsTable::class);

        add_action(
            'init',
            static function () use ($tableShortcode) {
                add_shortcode(
                    SpecificationsTable::KEY,
                    [$tableShortcode, 'render']
                );
            }
        );

        return true;
    }
}
