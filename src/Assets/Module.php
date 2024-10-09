<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Assets;

use Inpsyde\Modularity\Module\ExecutableModule;
use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;
use Inpsyde\Modularity\Package;
use Psr\Container\ContainerInterface;

final class Module implements ServiceModule, ExecutableModule
{
    use ModuleClassNameIdTrait;

    public function services(): array
    {
        return [
            AssetHelper::class => static fn (ContainerInterface $container) => new AssetHelper(
                $container->get(Package::PROPERTIES)->basePath() . 'assets',
                (string) $container->get(Package::PROPERTIES)->baseUrl() . 'assets'
            ),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        return true;
    }
}
