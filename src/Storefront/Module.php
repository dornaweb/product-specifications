<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Storefront;

use Amiut\ProductSpecs\ProductValues\ProductValueRepository;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetRepository;
use Amiut\ProductSpecs\Upgrade\MigrationState;
use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;
use Psr\Container\ContainerInterface;

final class Module implements ServiceModule
{
    use ModuleClassNameIdTrait;

    public function services(): array
    {
        return [
            SpecificationsTableReader::class => static fn (ContainerInterface $container) =>
                new SpecificationsTableReader(
                    $container->get(ProductValueRepository::class),
                    $container->get(SpecificationSetRepository::class),
                    $container->get(SpecificationDefinitionRepository::class),
                    $container->get(SpecificationTypeRegistry::class),
                    $container->get(MigrationState::class)
                ),
        ];
    }
}
