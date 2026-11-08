<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit;

use Amiut\ProductSpecs\Capability\Capabilities;
use Amiut\ProductSpecs\Capability\Module as CapabilityModule;
use Amiut\ProductSpecs\Migration\BackgroundMigration;
use Amiut\ProductSpecs\Migration\MigrationCommand;
use Amiut\ProductSpecs\Migration\MigrationNotice;
use Amiut\ProductSpecs\Migration\MigrationRestController;
use Amiut\ProductSpecs\Migration\MigrationScreen;
use Amiut\ProductSpecs\Migration\MigrationRunner;
use Amiut\ProductSpecs\Migration\MigrationRunRepository;
use Amiut\ProductSpecs\Migration\Module as MigrationModule;
use Amiut\ProductSpecs\Migration\Step\ProductValuesStep;
use Amiut\ProductSpecs\Migration\Step\SpecificationDefinitionsStep;
use Amiut\ProductSpecs\Migration\Step\SpecificationSetsStep;
use Amiut\ProductSpecs\ProductValues\Module as ProductValuesModule;
use Amiut\ProductSpecs\ProductValues\ProductValueRepository;
use Amiut\ProductSpecs\ProductValues\WordPressProductValueRepository;
use Amiut\ProductSpecs\Specification\Module as SpecificationModule;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionMapper;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\SpecificationTaxonomy;
use Amiut\ProductSpecs\Specification\Rest\SetUsageCounter;
use Amiut\ProductSpecs\Specification\Rest\SlugGuard;
use Amiut\ProductSpecs\Specification\Rest\SpecificationCollectionQuery;
use Amiut\ProductSpecs\Specification\Rest\SpecificationRestField;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use Amiut\ProductSpecs\SpecificationSet\DraftSlug;
use Amiut\ProductSpecs\SpecificationSet\Module as SpecificationSetModule;
use Amiut\ProductSpecs\SpecificationSet\Rest\ProductUsageCounter;
use Amiut\ProductSpecs\SpecificationSet\Rest\SpecificationSetRestField;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetMapper;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetPostType;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetRepository;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetRepositoryImpl;
use Amiut\ProductSpecs\SpecificationSet\StructureMetaDefinition;
use Amiut\ProductSpecs\SpecificationSet\StructureMetaRegistrar;
use Amiut\ProductSpecs\SpecificationSet\StructureSanitizer;
use Amiut\ProductSpecs\Tests\Unit\Support\StubContainer;
use Amiut\ProductSpecs\Upgrade\DataVersionRepository;
use Amiut\ProductSpecs\Upgrade\Installer;
use Amiut\ProductSpecs\Upgrade\LegacyDataProbe;
use Amiut\ProductSpecs\Upgrade\MigrationState;
use Amiut\ProductSpecs\Upgrade\Module as UpgradeModule;
use PHPUnit\Framework\TestCase;

/**
 * The container resolves services lazily, so a factory that cannot build its
 * service stays silent until the first consumer asks for it.
 *
 * Only the modules that build without WordPress are covered. Anything reaching
 * for Package::PROPERTIES needs a booted plugin and belongs to the integration
 * suite.
 */
final class ServiceWiringTest extends TestCase
{
    /**
     * @return array<string, class-string>
     */
    private static function expectations(): array
    {
        return [
            Capabilities::class => Capabilities::class,
            SpecificationSetPostType::class => SpecificationSetPostType::class,
            StructureMetaDefinition::class => StructureMetaDefinition::class,
            StructureSanitizer::class => StructureSanitizer::class,
            StructureMetaRegistrar::class => StructureMetaRegistrar::class,
            SpecificationSetMapper::class => SpecificationSetMapper::class,
            SpecificationSetRepository::class => SpecificationSetRepositoryImpl::class,
            ProductUsageCounter::class => ProductUsageCounter::class,
            DraftSlug::class => DraftSlug::class,
            SpecificationSetRestField::class => SpecificationSetRestField::class,
            SpecificationTaxonomy::class => SpecificationTaxonomy::class,
            SpecificationTypeRegistry::class => SpecificationTypeRegistry::class,
            SetUsageCounter::class => SetUsageCounter::class,
            SlugGuard::class => SlugGuard::class,
            SpecificationCollectionQuery::class => SpecificationCollectionQuery::class,
            SpecificationRestField::class => SpecificationRestField::class,
            SpecificationDefinitionMapper::class => SpecificationDefinitionMapper::class,
            SpecificationDefinitionRepository::class => SpecificationDefinitionRepository::class,
            DataVersionRepository::class => DataVersionRepository::class,
            LegacyDataProbe::class => LegacyDataProbe::class,
            Installer::class => Installer::class,
            MigrationState::class => MigrationState::class,
            MigrationRunRepository::class => MigrationRunRepository::class,
            MigrationRunner::class => MigrationRunner::class,
            MigrationCommand::class => MigrationCommand::class,
            BackgroundMigration::class => BackgroundMigration::class,
            MigrationScreen::class => MigrationScreen::class,
            MigrationNotice::class => MigrationNotice::class,
            MigrationRestController::class => MigrationRestController::class,
            SpecificationDefinitionsStep::class => SpecificationDefinitionsStep::class,
            SpecificationSetsStep::class => SpecificationSetsStep::class,
            ProductValuesStep::class => ProductValuesStep::class,
            ProductValueRepository::class => WordPressProductValueRepository::class,
        ];
    }

    /**
     * @return array<string, callable(\Psr\Container\ContainerInterface): mixed>
     */
    private static function declaredServices(): array
    {
        return array_merge(
            (new CapabilityModule())->services(),
            (new SpecificationSetModule())->services(),
            (new SpecificationModule())->services(),
            (new UpgradeModule())->services(),
            (new MigrationModule())->services(),
            array_intersect_key(
                (new ProductValuesModule())->services(),
                [ProductValueRepository::class => true]
            )
        );
    }

    /**
     * @return array<string, array{string, class-string}>
     */
    public static function serviceProvider(): array
    {
        $cases = [];

        foreach (self::expectations() as $id => $expected) {
            $cases[$id] = [$id, $expected];
        }

        return $cases;
    }

    /**
     * @param class-string $expected
     * @dataProvider serviceProvider
     */
    public function testServiceResolvesToItsConcreteType(string $id, string $expected): void
    {
        $container = new StubContainer(self::declaredServices());

        static::assertInstanceOf($expected, $container->get($id));
    }

    public function testEveryDeclaredServiceIsCovered(): void
    {
        $declared = array_keys(self::declaredServices());
        $covered = array_keys(self::expectations());

        sort($declared);
        sort($covered);

        static::assertSame(
            $declared,
            $covered,
            'A service was added to a module without being covered here.'
        );
    }
}
