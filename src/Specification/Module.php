<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification;

use Amiut\ProductSpecs\SpecificationSet\SpecificationSetPostType;
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
            SpecificationTaxonomy::class => static fn () => new SpecificationTaxonomy(),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        add_action(
            'init',
            static function () use ($container) {
                $taxonomy = $container->get(SpecificationTaxonomy::class);

                register_taxonomy(
                    $taxonomy->key(),
                    SpecificationSetPostType::KEY,
                    $taxonomy->args()
                );
            }
        );

        return true;
    }
}
