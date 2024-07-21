<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Settings;

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
            SettingsPage::class => static fn (ContainerInterface $container) => new SettingsPage(
                $container->get(TemplateRenderer::class),
            ),
            SettingsRegistrar::class => static fn (ContainerInterface $container) => new SettingsRegistrar()
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

        add_action(
            'admin_init',
            [$container->get(SettingsRegistrar::class), 'register']
        );

        return true;
    }

    public function registerAdminMenuPage(ContainerInterface $container): void
    {
        add_submenu_page(
            'dw-specs',
            esc_html__('Product specifications settings', 'product-specifications'),
            esc_html__('Settings', 'product-specifications'),
            'edit_pages',
            'dw-specs-settings',
            [$container->get(SettingsPage::class), 'render']
        );
    }
}
