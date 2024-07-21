<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Repository;

use Amiut\ProductSpecs\Attribute\AttributeFieldFactory;
use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;
use Psr\Container\ContainerInterface;

final class Module implements ServiceModule
{
    use ModuleClassNameIdTrait;

    public function services(): array
    {
        return [
            SpecificationsTableRepository::class => static fn () => new SpecificationsTableRepository(),
            AttributeFieldRepository::class => static fn (ContainerInterface $container) => new AttributeFieldRepository(
                $container->get(AttributeFieldFactory::class)
            ),
        ];
    }
}
