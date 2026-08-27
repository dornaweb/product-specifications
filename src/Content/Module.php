<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Content;

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
            Taxonomy\LegacyGroupTaxonomy::class => static fn () => new Taxonomy\LegacyGroupTaxonomy(),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        add_action(
            'init',
            static function () use ($container) {
                $legacyGroupTaxonomy = $container->get(Taxonomy\LegacyGroupTaxonomy::class);

                register_taxonomy(
                    $legacyGroupTaxonomy->key(),
                    SpecificationSetPostType::KEY,
                    $legacyGroupTaxonomy->args(),
                );
            },
        );

        return true;
    }
}
