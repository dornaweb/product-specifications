<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Assets;

use Inpsyde\Modularity\Module\ExecutableModule;
use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;
use Inpsyde\Modularity\Package;
use Inpsyde\Modularity\Properties\PluginProperties;
use Psr\Container\ContainerInterface;

final class Module implements ServiceModule, ExecutableModule
{
    use ModuleClassNameIdTrait;

    public function services(): array
    {
        return [
            AssetHelper::class => static function (ContainerInterface $container): AssetHelper {
                /** @var PluginProperties $properties */
                $properties = $container->get(Package::PROPERTIES);

                return new AssetHelper(
                    $properties->basePath() . 'assets',
                    (string) $properties->baseUrl() . 'assets'
                );
            },
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        return true;
    }
}
