<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Content;

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
            PostType\SpecificationsTable::class => static fn () => new PostType\SpecificationsTable(),
            Taxonomy\AttributeGroup::class => static fn () => new Taxonomy\AttributeGroup(),
            Taxonomy\Attribute::class => static fn () => new Taxonomy\Attribute(),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        add_action(
            'init',
            static function () use ($container) {
                $specificationsTablePostType = $container->get(PostType\SpecificationsTable::class);

                register_post_type(
                    $specificationsTablePostType->key(),
                    $specificationsTablePostType->args()
                );

                $attributGroupTaxonomy = $container->get(Taxonomy\AttributeGroup::class);
                $attributeTaxonomy = $container->get(Taxonomy\Attribute::class);

                register_taxonomy(
                    $attributGroupTaxonomy->key(),
                    $specificationsTablePostType->key(),
                    $attributGroupTaxonomy->args(),
                );

                register_taxonomy(
                    $attributeTaxonomy->key(),
                    $specificationsTablePostType->key(),
                    $attributeTaxonomy->args(),
                );
            },
        );

        return true;
    }
}
