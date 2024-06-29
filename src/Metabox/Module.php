<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Metabox;

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
            Metaboxes::class => static fn () => new Metaboxes(),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        add_action(
            'init',
            $container->get(Metaboxes::class)
        );

        add_action(
            'add_meta_boxes',
            [$container->get(Metaboxes::class), 'setup'],
            10,
            2
        );

        add_action(
            'wp_insert_post',
            [$container->get(Metaboxes::class), 'save'],
            10,
            2
        );

        return true;
    }
}
