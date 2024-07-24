<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\EntityUpdaterUi;

use Amiut\ProductSpecs\EntityUpdater\AttributeController;
use Amiut\ProductSpecs\EntityUpdater\AttributeGroupController;
use Amiut\ProductSpecs\EntityUpdater\AttributeSyncHandler;
use Amiut\ProductSpecs\Repository\AttributesRepository;
use Amiut\ProductSpecs\Template\TemplateRenderer;
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
            EditFormUi::class => static fn (ContainerInterface $container) => new EditFormUi(
                $container->get(TemplateRenderer::class)
            ),
            GroupReArrangeFormUi::class => static fn (ContainerInterface $container) => new GroupReArrangeFormUi(
                $container->get(TemplateRenderer::class),
                $container->get(AttributesRepository::class)
            ),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        add_action(
            'wp_ajax_dwps_edit_form',
            [$container->get(EditFormUi::class), 'render']
        );

        add_action(
            'wp_ajax_dwps_group_rearrange_form',
            [$container->get(GroupReArrangeFormUi::class), 'render']
        );

        return true;
    }
}
