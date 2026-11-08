<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport;

use Amiut\ProductSpecs\Capability\Capabilities;
use Amiut\ProductSpecs\ImportExport\Import\BackgroundImport;
use Amiut\ProductSpecs\ImportExport\Import\ImportRestController;
use Amiut\ProductSpecs\ImportExport\Import\ImportRunner;
use Amiut\ProductSpecs\ImportExport\Import\ImportRunRepository;
use Amiut\ProductSpecs\ImportExport\Import\ImportStorage;
use Amiut\ProductSpecs\ImportExport\Section\SetsSection;
use Amiut\ProductSpecs\ImportExport\Section\SpecificationsSection;
use Amiut\ProductSpecs\ImportExport\Section\ValuesSection;
use Amiut\ProductSpecs\ProductValues\ProductValueRepository;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\Type\OptionsChecker;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetRepository;
use Amiut\ProductSpecs\Upgrade\MigrationState;
use Inpsyde\Modularity\Module\ExecutableModule;
use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;
use Inpsyde\Modularity\Package as PluginPackage;
use Inpsyde\Modularity\Properties\PluginProperties;
use Psr\Container\ContainerInterface;
use WP_CLI;

final class Module implements ServiceModule, ExecutableModule
{
    use ModuleClassNameIdTrait;

    public function services(): array
    {
        return [
            SectionRegistry::class => static fn () => new SectionRegistry(),
            SpecificationsSection::class => static fn (ContainerInterface $container) => new SpecificationsSection(
                $container->get(SpecificationDefinitionRepository::class),
                $container->get(SpecificationTypeRegistry::class),
                $container->get(OptionsChecker::class),
                $container->get(ProductValueRepository::class)
            ),
            SetsSection::class => static fn (ContainerInterface $container) => new SetsSection(
                $container->get(SpecificationSetRepository::class),
                $container->get(SpecificationDefinitionRepository::class)
            ),
            ValuesSection::class => static fn (ContainerInterface $container) => new ValuesSection(
                $container->get(ProductValueRepository::class),
                $container->get(SpecificationDefinitionRepository::class),
                $container->get(SpecificationSetRepository::class),
                $container->get(SpecificationTypeRegistry::class),
                [new ProductMatcher()]
            ),
            PackageReader::class => static fn (ContainerInterface $container) => new PackageReader(
                $container->get(SectionRegistry::class)
            ),
            PackageExporter::class => static function (ContainerInterface $container): PackageExporter {
                /** @var PluginProperties $properties */
                $properties = $container->get(PluginPackage::PROPERTIES);

                return new PackageExporter($container->get(SectionRegistry::class), $properties->version());
            },
            ExportDownload::class => static fn (ContainerInterface $container) => new ExportDownload(
                $container->get(PackageExporter::class),
                $container->get(SectionRegistry::class),
                $container->get(Capabilities::class),
                $container->get(MigrationState::class)
            ),
            PackageCommand::class => static fn (ContainerInterface $container) => new PackageCommand(
                $container->get(ExportDownload::class),
                $container->get(PackageReader::class),
                $container->get(ImportPlanner::class),
                $container->get(PackageImporter::class),
                $container->get(MigrationState::class)
            ),
            ImportPlanner::class => static fn (ContainerInterface $container) => new ImportPlanner(
                $container->get(SectionRegistry::class)
            ),
            PackageImporter::class => static fn (ContainerInterface $container) => new PackageImporter(
                $container->get(SectionRegistry::class)
            ),
            ImportRunRepository::class => static fn () => new ImportRunRepository(),
            ImportStorage::class => static fn () => new ImportStorage(),
            ImportRunner::class => static fn (ContainerInterface $container) => new ImportRunner(
                $container->get(ImportRunRepository::class),
                $container->get(ImportStorage::class),
                $container->get(PackageReader::class),
                $container->get(ImportPlanner::class),
                $container->get(PackageImporter::class),
                $container->get(MigrationState::class)
            ),
            BackgroundImport::class => static fn (ContainerInterface $container) => new BackgroundImport(
                $container->get(ImportRunner::class)
            ),
            ImportRestController::class => static fn (ContainerInterface $container) => new ImportRestController(
                $container->get(ImportRunner::class),
                $container->get(BackgroundImport::class),
                $container->get(Capabilities::class)
            ),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        add_action(
            SectionRegistry::REGISTER_ACTION,
            static function (SectionRegistry $registry) use ($container): void {
                $registry->register($container->get(SpecificationsSection::class));
                $registry->register($container->get(SetsSection::class));
                $registry->register($container->get(ValuesSection::class));
            },
            0
        );

        add_action(
            'init',
            static function () use ($container): void {
                do_action(SectionRegistry::REGISTER_ACTION, $container->get(SectionRegistry::class));
            }
        );

        add_action(
            'admin_post_' . ExportDownload::ACTION,
            static function () use ($container): void {
                $container->get(ExportDownload::class)->handle();
            }
        );

        add_action(
            BackgroundImport::HOOK,
            static function () use ($container): void {
                $container->get(BackgroundImport::class)->process();
            }
        );

        add_action(
            'rest_api_init',
            static function () use ($container): void {
                $container->get(ImportRestController::class)->registerRoutes();
            }
        );

        if (class_exists(WP_CLI::class)) {
            WP_CLI::add_command(PackageCommand::EXPORT, [$container->get(PackageCommand::class), 'export']);
            WP_CLI::add_command(PackageCommand::IMPORT, [$container->get(PackageCommand::class), 'import']);
        }

        return true;
    }
}
