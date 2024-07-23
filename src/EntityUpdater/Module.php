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
            AttributeController::class => static fn () => new AttributeController(),
            AttributeGroupController::class => static fn () => new AttributeGroupController(),
            AttributeSyncHandler::class => static fn () => new AttributeSyncHandler(),
            AttributeGroupArrangementUpdater::class => static fn () => new AttributeGroupArrangementUpdater(),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        add_action(
            'wp_ajax_' . AttributeController::AJAX_ACTION,
            $container->get(AttributeController::class)
        );

        add_action(
            'wp_ajax_' . AttributeGroupController::AJAX_ACTION,
            $container->get(AttributeGroupController::class)
        );

        add_action(
            AttributeController::ACTION_ATTRIBUTES_DELETED,
            [$container->get(AttributeSyncHandler::class), 'whenDeleted'],
            10,
            2
        );

        add_action(
            AttributeController::ACTION_ATTRIBUTES_ADDED,
            [$container->get(AttributeSyncHandler::class), 'whenAdded']
        );

        add_action(
            AttributeController::ACTION_ATTRIBUTES_UPDATED,
            [$container->get(AttributeSyncHandler::class), 'whenAdded']
        );

        add_action(
            'wp_ajax_dwps_group_rearrange',
            $container->get(AttributeGroupArrangementUpdater::class)
        );

        return true;
    }
}
