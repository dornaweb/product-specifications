<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Comparison;

use Amiut\ProductSpecs\Comparison\ComparedProductIds;
use Amiut\ProductSpecs\Comparison\Comparison;
use Amiut\ProductSpecs\Comparison\ComparisonGroup;
use Amiut\ProductSpecs\Comparison\ComparisonReader;
use Amiut\ProductSpecs\Comparison\ComparisonRow;
use Amiut\ProductSpecs\Comparison\ProductCatalog;
use Amiut\ProductSpecs\Migration\MigrationState;
use Amiut\ProductSpecs\ProductValues\WordPressProductValueRepository;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionMapper;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use Amiut\ProductSpecs\Specification\Type\TextType;
use Amiut\ProductSpecs\SpecificationSet\SpecificationGroup;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSet;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetItem;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetPresentation;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetRepository;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetStructure;
use Amiut\ProductSpecs\Storefront\ValueRenderer;
use Amiut\ProductSpecs\Tests\Unit\ImportExport\InMemorySetRepository;
use Amiut\ProductSpecs\Upgrade\DataVersionRepository;
use PHPUnit\Framework\TestCase;
use WC_Product;
use WP_Term;

final class ComparisonReaderTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['wp_test_options'] = [DataVersionRepository::OPTION => 2];
        $GLOBALS['wp_test_terms'] = [];
        $GLOBALS['wp_test_term_meta'] = [];
        $GLOBALS['wp_test_post_meta'] = [];

        $this->specification(1, 'Screen', 'screen');
        $this->specification(2, 'Storage', 'storage');
        $this->specification(3, 'Notes', 'notes');

        $this->product(10, 'phones', ['screen' => '6.1 in', 'storage' => '128 GB']);
        $this->product(11, 'phones', ['screen' => '6.7 in']);
        $this->product(12, 'laptops', ['screen' => '14 in']);
        $this->product(13, null, []);
        $this->product(14, 'phones', ['storage' => '256 GB']);
        $this->product(15, 'drafts', ['screen' => '5 in']);
        $this->product(16, 'closed', ['screen' => '5 in']);
    }

    public function testTheFirstProductWithAComparableSetDecidesWhichProductsStay(): void
    {
        $comparison = $this->reader()->read(ComparedProductIds::fromQuery('13,15,16,11,12,10'), 10);

        self::assertSame([11, 10], $comparison->ids()->ids());
        self::assertSame('phones', $comparison->setSlug());
    }

    public function testDroppedProductsUseNoSlotAndTheRestIsCutAtTheMaximum(): void
    {
        $comparison = $this->reader()->read(ComparedProductIds::fromQuery('12,10,13,11,14'), 2);

        self::assertSame([12], $comparison->ids()->ids());

        $comparison = $this->reader()->read(ComparedProductIds::fromQuery('10,12,13,11,14'), 2);

        self::assertSame([10, 11], $comparison->ids()->ids());
    }

    public function testARowStaysWhenOneProductHasAValueAndAnEmptyGroupGoes(): void
    {
        $comparison = $this->reader()->read(ComparedProductIds::fromQuery('11,10'), 3);

        self::assertSame(
            [['Display', [['Screen', true, true], ['Storage', false, true]]]],
            $this->outline($comparison)
        );
    }

    public function testNoIdsMeansNoProductQuery(): void
    {
        $catalog = $this->catalog();

        self::assertTrue($this->reader($catalog)->read(ComparedProductIds::fromQuery('abc'), 3)->isEmpty());
        self::assertSame(0, $catalog->calls);
    }

    public function testNothingIsComparedWhileAMigrationIsRequired(): void
    {
        $GLOBALS['wp_test_options'][DataVersionRepository::OPTION] = 1;

        self::assertTrue($this->reader()->read(ComparedProductIds::fromQuery('10,11'), 3)->isEmpty());
    }

    /**
     * @return list<array{string, list<array{string, bool, bool}>}>
     */
    private function outline(Comparison $comparison): array
    {
        return array_map(
            static fn (ComparisonGroup $group): array => [
                $group->label(),
                array_map(
                    static fn (ComparisonRow $row): array => [
                        $row->name(),
                        $row->cells()[0] !== null,
                        $row->cells()[1] !== null,
                    ],
                    $group->rows()
                ),
            ],
            $comparison->groups()
        );
    }

    private function reader(?ProductCatalog $catalog = null): ComparisonReader
    {
        $structure = new SpecificationSetStructure(1, [
            new SpecificationGroup('display', 'Display', '', true, [
                SpecificationSetItem::create('screen'),
                SpecificationSetItem::create('storage'),
                SpecificationSetItem::create('notes'),
            ]),
            new SpecificationGroup('extra', 'Extra', '', true, [SpecificationSetItem::create('notes')]),
        ]);
        $comparable = SpecificationSetPresentation::forNewSet();

        $sets = new InMemorySetRepository([
            'phones' => SpecificationSet::fromStorage(1, 'Phones', 'publish', $structure, 'phones', '', $comparable),
            'laptops' => SpecificationSet::fromStorage(2, 'Laptops', 'publish', $structure, 'laptops', '', $comparable),
            'drafts' => SpecificationSet::fromStorage(3, 'Drafts', 'draft', $structure, 'drafts', '', $comparable),
            'closed' => SpecificationSet::fromStorage(4, 'Closed', 'publish', $structure, 'closed'),
        ]);

        return new ComparisonReader(
            $catalog ?? $this->catalog(),
            new WordPressProductValueRepository(),
            $sets,
            new SpecificationDefinitionRepository(new SpecificationDefinitionMapper()),
            new ValueRenderer(new SpecificationTypeRegistry(new TextType())),
            new MigrationState(new DataVersionRepository())
        );
    }

    /**
     * @return ProductCatalog&object{calls: int}
     */
    private function catalog(): ProductCatalog
    {
        return new class implements ProductCatalog {
            public int $calls = 0;

            public function comparableProducts(array $ids): array
            {
                $this->calls++;

                return array_map(static fn (int $id): WC_Product => new WC_Product($id), $ids);
            }

            public function searchInSet(string $setSlug, string $search, array $exclude, int $limit): array
            {
                return [];
            }

            public function isComparable(WC_Product $product): bool
            {
                return true;
            }
        };
    }

    /**
     * @param array<string, string> $values
     */
    private function product(int $id, ?string $set, array $values): void
    {
        $GLOBALS['wp_test_post_meta'][$id] = [
            WordPressProductValueRepository::VALUES_META_KEY => ['schema_version' => 1, 'values' => $values],
        ];

        if ($set !== null) {
            $GLOBALS['wp_test_post_meta'][$id][WordPressProductValueRepository::SET_META_KEY] = $set;
        }
    }

    private function specification(int $id, string $name, string $slug): void
    {
        $term = new WP_Term();
        $term->term_id = $id;
        $term->name = $name;
        $term->slug = $slug;
        $GLOBALS['wp_test_terms'][$id] = $term;
        $GLOBALS['wp_test_term_meta'][$id] = ['attr_type' => TextType::ID];
    }
}
