<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Admin;

use Amiut\ProductSpecs\Assets\AssetHelper;
use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;
use Psr\Container\ContainerInterface;
use Inpsyde\Modularity\Module\ExecutableModule;

final class Module implements ServiceModule, ExecutableModule
{
    use ModuleClassNameIdTrait;

    public function services(): array
    {
        return [
            Assets::class => static fn (ContainerInterface $container) => new Assets(
                $container->get(AssetHelper::class)
            ),
            AdminPageTopMenuModifier::class => static fn () => new AdminPageTopMenuModifier(),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        add_action(
            'admin_enqueue_scripts',
            [$container->get(Assets::class), 'load'],
            30
        );

        add_action(
            'admin_menu',
            function () use ($container) {
                $this->registerMenuPages($container);
            }
        );

        add_action(
            'admin_menu',
            [$container->get(AdminPageTopMenuModifier::class), 'modify']
        );

        return true;
    }

    private function registerMenuPages(ContainerInterface $container): void
    {
        wc_admin_register_page(
            [
                'id'       => 'dwps-product-specifications',
                'title'    => __('Product Specifications', 'product-specifications'),
                'parent'   => 'woocommerce',
                'capability' => 'shop_manager',
                'nav_args' => [
                    'parent' => 'woocommerce',
                    'order' => 50,
                ],
                'path' => '/dwps-product-specifications',
            ]
        );

        add_menu_page(
            esc_html__('Product specifications table', 'product-specifications'),
            esc_html__('Specs. tables', 'product-specifications'),
            'edit_pages',
            'dw-specs',
            static function (): void {
            },
            'dashicons-welcome-view-site',
            25
        );

        // Add tables page
        add_submenu_page(
            'dw-specs',
            esc_html__('Add a new table', 'product-specifications'),
            esc_html__('New table', 'product-specifications'),
            'edit_pages',
            'dw-specs-new',
            static function (): void {
            }
        );
    }
}
