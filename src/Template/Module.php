<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Template;

use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;
use Inpsyde\Modularity\Package;
use Psr\Container\ContainerInterface;

final class Module implements ServiceModule
{
    use ModuleClassNameIdTrait;

    public function services(): array
    {
        return [
            TemplateRenderer::class => static fn (ContainerInterface $container) => new PhpTemplateRenderer(
                new Context(
                    [
                        $container->get(Package::PROPERTIES)->basePath() . 'templates',
                    ]
                )
            ),
        ];
    }
}
