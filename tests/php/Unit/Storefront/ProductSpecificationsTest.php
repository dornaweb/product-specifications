<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Storefront;

use Amiut\ProductSpecs\Migration\MigrationState;
use Amiut\ProductSpecs\ProductValues\WordPressProductValueRepository;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionMapper;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use Amiut\ProductSpecs\Specification\Type\TextType;
use Amiut\ProductSpecs\Specification\Type\TrueFalseType;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetMapper;
use Amiut\ProductSpecs\SpecificationSet\WordPressSpecificationSetRepository;
use Amiut\ProductSpecs\Storefront\ProductSpecifications;
use Amiut\ProductSpecs\Storefront\SpecificationsTable;
use Amiut\ProductSpecs\Storefront\SpecificationsTableReader;
use Amiut\ProductSpecs\Storefront\TableGroup;
use Amiut\ProductSpecs\Storefront\TableRow;
use Amiut\ProductSpecs\Upgrade\DataVersion;
use Amiut\ProductSpecs\Upgrade\DataVersionRepository;
use PHPUnit\Framework\TestCase;
use WC_Product;
use WP_Term;

final class ProductSpecificationsTest extends TestCase
{
    private const PRODUCT = 20;

    protected function setUp(): void
    {
        $term = new WP_Term();
        $term->term_id = 2;
        $term->name = 'Wi-Fi';
        $term->slug = 'wifi';
        $GLOBALS['wp_test_terms'] = [2 => $term];
        $GLOBALS['wp_test_term_meta'] = [2 => ['attr_type' => 'true_false', 'attr_values' => [], 'attr_default' => '']];
        $GLOBALS['wp_test_options'] = [DataVersionRepository::OPTION => DataVersion::CURRENT_VERSION];
        $GLOBALS['wp_test_post_meta'] = [
            self::PRODUCT => [
                WordPressProductValueRepository::VALUES_META_KEY => [
                    'schema_version' => 1,
                    'values' => ['wifi' => 'yes'],
                ],
            ],
        ];
        $GLOBALS['wp_test_filters'] = [];
    }

    protected function tearDown(): void
    {
        $GLOBALS['wp_test_filters'] = [];
    }

    public function testATableFilterCanReplaceTheTable(): void
    {
        $replacement = new SpecificationsTable([
            new TableGroup('extra', 'Extra', [new TableRow('origin', 'Origin', 'Germany')]),
        ]);
        $GLOBALS['wp_test_filters'][ProductSpecifications::TABLE_FILTER] =
            static fn (SpecificationsTable $table, WC_Product $product): SpecificationsTable => $replacement;

        self::assertSame($replacement, $this->specifications()->table(new WC_Product(self::PRODUCT)));
    }

    public function testATableFilterReturningSomethingElseLeavesTheTableAsItWas(): void
    {
        $GLOBALS['wp_test_filters'][ProductSpecifications::TABLE_FILTER] = static fn (): string => '<p>broken</p>';

        self::assertInstanceOf(
            SpecificationsTable::class,
            $this->specifications()->table(new WC_Product(self::PRODUCT))
        );
    }

    public function testAValueIsReadAsItsSpecificationsTypeReadsIt(): void
    {
        self::assertTrue($this->specifications()->value(new WC_Product(self::PRODUCT), 'wifi'));
    }

    public function testAnUnknownSpecificationHasNoValue(): void
    {
        self::assertNull($this->specifications()->value(new WC_Product(self::PRODUCT), 'ghost'));
    }

    public function testNoValueIsReadBeforeTheMigrationCompletes(): void
    {
        $GLOBALS['wp_test_options'][DataVersionRepository::OPTION] = DataVersion::LEGACY_VERSION;

        self::assertNull($this->specifications()->value(new WC_Product(self::PRODUCT), 'wifi'));
    }

    private function specifications(): ProductSpecifications
    {
        $values = new WordPressProductValueRepository();
        $definitions = new SpecificationDefinitionRepository(new SpecificationDefinitionMapper());
        $types = new SpecificationTypeRegistry(new TextType());
        $types->register(new TrueFalseType());
        $migration = new MigrationState(new DataVersionRepository());

        return new ProductSpecifications(
            new SpecificationsTableReader(
                $values,
                new WordPressSpecificationSetRepository(new SpecificationSetMapper()),
                $definitions,
                $types,
                $migration
            ),
            $values,
            $definitions,
            $types,
            $migration
        );
    }
}
