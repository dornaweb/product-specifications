<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration;

use Amiut\ProductSpecs\Capability\Capabilities;
use Amiut\ProductSpecs\Migration\Soft\LegacySettingsMigration;
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
use Amiut\ProductSpecs\Upgrade\MigrationState;
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
                $container->get(LegacyGroupTaxonomy::class),
                [
                    $container->get(SpecificationDefinitionsStep::class),
                    $container->get(SpecificationSetsStep::class),
                    $container->get(ProductValuesStep::class),
                ]
            ),
            BackgroundMigration::class => static fn (ContainerInterface $container) => new BackgroundMigration(
                $container->get(MigrationRunner::class)
            ),
            MigrationScreen::class => static fn () => new MigrationScreen(),
            LegacySettingsMigration::class => static fn () => new LegacySettingsMigration(),
            LegacyGroupTaxonomy::class => static fn () => new LegacyGroupTaxonomy(),
            MigrationNotice::class => static fn (ContainerInterface $container) => new MigrationNotice(
                $container->get(MigrationState::class),
                $container->get(MigrationRunRepository::class),
                $container->get(MigrationScreen::class),
                $container->get(Capabilities::class)
            ),
            MigrationRestController::class => static fn (ContainerInterface $container) => new MigrationRestController(
                $container->get(MigrationRunner::class),
                $container->get(BackgroundMigration::class),
                $container->get(DataVersionRepository::class),
                $container->get(Capabilities::class)
            ),
            MigrationCommand::class => static fn (ContainerInterface $container) => new MigrationCommand(
                $container->get(MigrationRunner::class)
            ),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        // Ahead of every other init callback, so nothing reads the data before it is moved.
        add_action(
            'init',
            static function () use ($container): void {
                if ($container->get(DataVersionRepository::class)->stored()->isMigrated()) {
                    return;
                }

                foreach (self::softMigrations($container) as $softMigration) {
                    $softMigration->migrate();
                }
            },
            -1
        );

        add_action(
            BackgroundMigration::HOOK,
            static function () use ($container): void {
                $container->get(BackgroundMigration::class)->process();
            }
        );

        add_action(
            'rest_api_init',
            static function () use ($container): void {
                $container->get(MigrationRestController::class)->registerRoutes();
            }
        );

        add_action(
            'admin_init',
            static function () use ($container): void {
                if (!wp_doing_ajax()) {
                    $container->get(MigrationRunner::class)->completeIfNothingToMigrate();
                }
            }
        );

        add_action(
            'admin_notices',
            static function () use ($container): void {
                $container->get(MigrationNotice::class)->render();
            }
        );

        if (class_exists(WP_CLI::class)) {
            WP_CLI::add_command(MigrationCommand::NAME, $container->get(MigrationCommand::class));
        }

        return true;
    }

    /**
     * @return list<SoftMigration>
     */
    private static function softMigrations(ContainerInterface $container): array
    {
        return [
            $container->get(LegacySettingsMigration::class),
        ];
    }
}
