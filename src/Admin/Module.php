<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Admin;

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

        ];
    }

    public function run(ContainerInterface $container): bool
    {
        return true;
    }
}
