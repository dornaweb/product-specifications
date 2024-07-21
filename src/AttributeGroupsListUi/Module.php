<?php

namespace Amiut\ProductSpecs\AttributeGroupsListUi;

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
            AttributeGroupsListPage::class => static fn (ContainerInterface $container) => new AttributeGroupsListPage(
                $container->get(TemplateRenderer::class),
            ),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        add_action(
            'admin_menu',
            function () use ($container) {
                $this->registerAdminMenuPage($container);
            }
        );

        return true;
    }

    public function registerAdminMenuPage(ContainerInterface $container): void
    {
        add_submenu_page(
            'dw-specs',
            esc_html__('Attribute Groups', 'product-specifications'),
            esc_html__('Groups', 'product-specifications'),
            'edit_pages',
            'dw-specs-groups',
            [$container->get(AttributeGroupsListPage::class), 'render']
        );
    }
}
