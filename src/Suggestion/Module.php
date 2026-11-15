<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Suggestion;

use Amiut\ProductSpecs\ProductValues\ProductValueRepository;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
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
            ValueUsageRepository::class => static fn () => new ValueUsageRepository(),
            ValueUsageProjection::class => static fn (ContainerInterface $container) => new ValueUsageProjection(
                $container->get(ValueUsageRepository::class),
                $container->get(SpecificationDefinitionRepository::class),
                $container->get(ProductValueRepository::class)
            ),
            SuggestionsRestField::class => static fn (ContainerInterface $container) => new SuggestionsRestField(
                $container->get(SpecificationDefinitionRepository::class),
                $container->get(ValueUsageProjection::class)
            ),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        $container->get(ValueUsageProjection::class)->register();

        add_action(
            'rest_api_init',
            static function () use ($container): void {
                $container->get(SuggestionsRestField::class)->register();
            }
        );

        return true;
    }
}
