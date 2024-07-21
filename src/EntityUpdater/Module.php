<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\EntityUpdater;

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
            AttributeGroupController::class => static fn (ContainerInterface $container) => new AttributeGroupController(),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        add_action(
            'wp_ajax_' . AttributeGroupController::AJAX_ACTION,
            $container->get(AttributeGroupController::class)
        );

        return true;
    }
}
