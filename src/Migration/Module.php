<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration;

use Amiut\ProductSpecs\Migration\Step\ProductValuesStep;
use Amiut\ProductSpecs\Migration\Step\SetStructureBuilder;
use Amiut\ProductSpecs\Migration\Step\SnapshotValueReader;
use Amiut\ProductSpecs\Migration\Step\SpecificationDefinitionNormalizer;
use Amiut\ProductSpecs\Migration\Step\SpecificationDefinitionsStep;
use Amiut\ProductSpecs\Migration\Step\SpecificationSetsStep;
use Amiut\ProductSpecs\Migration\Step\WordPressSpecificationLookup;
use Amiut\ProductSpecs\ProductValues\ProductValueRepository;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetMapper;
use Amiut\ProductSpecs\Upgrade\DataVersionRepository;
use Inpsyde\Modularity\Module\ExecutableModule;
use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;
use Psr\Container\ContainerInterface;
use WP_CLI;

final class Module implements ServiceModule, ExecutableModule
{
    use ModuleClassNameIdTrait;

    public function services(): array
    {
        return [
            MigrationRunRepository::class => static fn () => new MigrationRunRepository(),
            SpecificationDefinitionsStep::class => static fn () => new SpecificationDefinitionsStep(
                new SpecificationDefinitionNormalizer()
            ),
            SpecificationSetsStep::class => static fn (ContainerInterface $container) => new SpecificationSetsStep(
                new SetStructureBuilder(),
                $container->get(SpecificationSetMapper::class)
            ),
            ProductValuesStep::class => static fn (ContainerInterface $container) => new ProductValuesStep(
                new SnapshotValueReader(
                    new WordPressSpecificationLookup($container->get(SpecificationDefinitionRepository::class)),
                    $container->get(SpecificationTypeRegistry::class)
                ),
                $container->get(ProductValueRepository::class)
            ),
            MigrationRunner::class => static fn (ContainerInterface $container) => new MigrationRunner(
                $container->get(MigrationRunRepository::class),
                $container->get(DataVersionRepository::class),
                [
                    $container->get(SpecificationDefinitionsStep::class),
                    $container->get(SpecificationSetsStep::class),
                    $container->get(ProductValuesStep::class),
                ]
            ),
            BackgroundMigration::class => static fn (ContainerInterface $container) => new BackgroundMigration(
                $container->get(MigrationRunner::class)
            ),
            MigrationCommand::class => static fn (ContainerInterface $container) => new MigrationCommand(
                $container->get(MigrationRunner::class)
            ),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        add_action(
            BackgroundMigration::HOOK,
            static function () use ($container): void {
                $container->get(BackgroundMigration::class)->process();
            }
        );

        if (class_exists(WP_CLI::class)) {
            WP_CLI::add_command(MigrationCommand::NAME, $container->get(MigrationCommand::class));
        }

        return true;
    }
}
