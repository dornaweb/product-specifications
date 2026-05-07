<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet;

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
            SpecificationSetPostType::class => static fn () => new SpecificationSetPostType(),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        add_action(
            'init',
            static function () use ($container) {
                $container->get(SpecificationSetPostType::class)->init();
            },
        );

        return true;
    }
}
