<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ProductSpecifications;

use Amiut\ProductSpecs\Metabox\Metaboxes;
use Amiut\ProductSpecs\Repository\AttributeFieldRepository;
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
            ProductSpecificationsMetabox::class => static fn (ContainerInterface $container) => new ProductSpecificationsMetabox(
                $container->get(TemplateRenderer::class),
                $container->get(AttributeFieldRepository::class)
            ),
            AjaxTablesHandler::class => static fn (ContainerInterface $container) => new AjaxTablesHandler(
                $container->get(TemplateRenderer::class),
                $container->get(AttributeFieldRepository::class)
            ),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        add_action(
            Metaboxes::ACTION_SETUP,
            static function (Metaboxes $metaboxes) use ($container) {
                $metaboxes->add($container->get(ProductSpecificationsMetabox::class));
            },
        );

        add_action(
            'wp_ajax_' . AjaxTablesHandler::ACTION_LOAD_TABLE,
            [$container->get(AjaxTablesHandler::class), 'loadTable']
        );

        return true;
    }
}
