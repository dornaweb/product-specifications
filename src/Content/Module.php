<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Content;

use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;
use Psr\Container\ContainerInterface;
use Inpsyde\Modularity\Module\ExecutableModule;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetPostType;

final class Module implements ServiceModule, ExecutableModule
{
    use ModuleClassNameIdTrait;

    public function services(): array
    {
        return [
            Taxonomy\AttributeGroup::class => static fn () => new Taxonomy\AttributeGroup(),
            Taxonomy\Attribute::class => static fn () => new Taxonomy\Attribute(),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        add_action(
            'init',
            static function () use ($container) {
                $attributGroupTaxonomy = $container->get(Taxonomy\AttributeGroup::class);
                $attributeTaxonomy = $container->get(Taxonomy\Attribute::class);

                register_taxonomy(
                    $attributGroupTaxonomy->key(),
                    SpecificationSetPostType::KEY,
                    $attributGroupTaxonomy->args(),
                );

                register_taxonomy(
                    $attributeTaxonomy->key(),
                    SpecificationSetPostType::KEY,
                    $attributeTaxonomy->args(),
                );
            },
        );

        return true;
    }
}
