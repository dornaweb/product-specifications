<?php

namespace Amiut\ProductSpecs\ImportExport;

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
            ImportDataAjaxHandler::class => static fn () => new ImportDataAjaxHandler(),
            ExportDataAjaxHandler::class => static fn () => new ExportDataAjaxHandler(),
            ImportExportPage::class => static fn (ContainerInterface $container) => new ImportExportPage(
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

        add_action(
            'wp_ajax_dwspecs_export_data',
            $container->get(ExportDataAjaxHandler::class)
        );

        add_action(
            'wp_ajax_dwspecs_import_data',
            $container->get(ImportDataAjaxHandler::class)
        );

        return true;
    }

    public function registerAdminMenuPage(ContainerInterface $container): void
    {
        add_submenu_page(
            'dw-specs',
            esc_html__('Product specifications Import/Export', 'product-specifications'),
            esc_html__('Import/export', 'product-specifications'),
            'edit_pages',
            'dw-specs-export',
            [$container->get(ImportExportPage::class), 'render']
        );
    }
}
