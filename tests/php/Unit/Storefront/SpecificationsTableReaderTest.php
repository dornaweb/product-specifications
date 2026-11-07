<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Storefront;

use Amiut\ProductSpecs\ProductValues\WordPressProductValueRepository;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionMapper;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use Amiut\ProductSpecs\Specification\Type\TextType;
use Amiut\ProductSpecs\Specification\Type\TrueFalseType;
use Amiut\ProductSpecs\SpecificationSet\SpecificationGroup;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSet;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetItem;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetRepository;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetStructure;
use Amiut\ProductSpecs\Storefront\SpecificationsTable;
use Amiut\ProductSpecs\Storefront\SpecificationsTableReader;
use Amiut\ProductSpecs\Storefront\TableGroup;
use Amiut\ProductSpecs\Storefront\TableRow;
use Amiut\ProductSpecs\Upgrade\DataVersionRepository;
use Amiut\ProductSpecs\Upgrade\MigrationState;
use PHPUnit\Framework\TestCase;
use WC_Product;
use WP_Term;

final class SpecificationsTableReaderTest extends TestCase
{
    private const PRODUCT = 10;

    protected function setUp(): void
    {
        $GLOBALS['wp_test_options'] = [DataVersionRepository::OPTION => 2];
        $GLOBALS['wp_test_terms'] = [];
        $GLOBALS['wp_test_term_meta'] = [];
        $GLOBALS['wp_test_post_meta'] = [];

        $this->specification(1, 'Weight', 'weight');
        $this->specification(2, 'Wi-Fi', 'wifi', TrueFalseType::ID);
        $this->specification(3, 'Notes', 'notes');
        $this->specification(4, 'Colour', 'colour');
        $this->specification(5, 'Battery', 'battery');

        $GLOBALS['wp_test_post_meta'][self::PRODUCT] = [
            WordPressProductValueRepository::SET_META_KEY => 'phones',
            WordPressProductValueRepository::VALUES_META_KEY => [
                'schema_version' => 1,
                'values' => [
                    'weight' => '180 g',
                    'wifi' => false,
                    'notes' => '',
                    'colour' => 'Blue',
                    'battery' => '5000 mAh',
                    'deleted' => 'kept but not shown',
                ],
            ],
        ];
    }

    public function testRowsFollowTheStructureAndSkipWhatIsHiddenEmptyOrGone(): void
    {
        $table = $this->reader($this->set('publish', [
            new SpecificationGroup('general', 'General', '', true, [
                SpecificationSetItem::create(4, 'colour', false),
                SpecificationSetItem::create(2, 'wifi'),
                SpecificationSetItem::create(1, 'weight'),
                SpecificationSetItem::create(3, 'notes'),
                SpecificationSetItem::create(99, 'deleted'),
            ]),
            new SpecificationGroup('power', 'Power', '', false, [
                SpecificationSetItem::create(5, 'battery'),
            ]),
            new SpecificationGroup('empty', 'Nothing filled', '', true, [
                SpecificationSetItem::create(3, 'notes'),
            ]),
        ]))->forProduct(new WC_Product(self::PRODUCT));

        self::assertSame(
            [['General', ['Wi-Fi', 'Weight']]],
            $this->outline($table)
        );
        self::assertStringContainsString('ps-yes-no--no', $table->groups()[0]->rows()[0]->value());
    }

    /**
     * @dataProvider unrenderedCases
     */
    public function testNothingRendersForAnUnpublishedSetNoSetOrAPendingMigration(string $status, int $version): void
    {
        $GLOBALS['wp_test_options'][DataVersionRepository::OPTION] = $version;
        $set = $this->set($status, [
            new SpecificationGroup('general', 'General', '', true, [SpecificationSetItem::create(1, 'weight')]),
        ]);

        self::assertTrue($this->reader($set)->forProduct(new WC_Product(self::PRODUCT))->isEmpty());
    }

    /**
     * @return array<string, array{string, int}>
     */
    public static function unrenderedCases(): array
    {
        return [
            'a draft set' => ['draft', 2],
            'a set in the trash' => ['trash', 2],
            'a migration still required' => ['publish', 1],
        ];
    }

    public function testAProductWithNoSetGetsAnEmptyTable(): void
    {
        unset($GLOBALS['wp_test_post_meta'][self::PRODUCT][WordPressProductValueRepository::SET_META_KEY]);

        self::assertTrue($this->reader(null)->forProduct(new WC_Product(self::PRODUCT))->isEmpty());
    }

    /**
     * @return list<array{string, list<string>}>
     */
    private function outline(SpecificationsTable $table): array
    {
        return array_map(
            static fn (TableGroup $group): array => [
                $group->label(),
                array_map(static fn (TableRow $row): string => $row->name(), $group->rows()),
            ],
            $table->groups()
        );
    }

    private function specification(int $id, string $name, string $slug, string $type = TextType::ID): void
    {
        $term = new WP_Term();
        $term->term_id = $id;
        $term->name = $name;
        $term->slug = $slug;
        $GLOBALS['wp_test_terms'][$id] = $term;
        $GLOBALS['wp_test_term_meta'][$id] = ['attr_type' => $type];
    }

    /**
     * @param list<SpecificationGroup> $groups
     */
    private function set(string $status, array $groups): SpecificationSet
    {
        return SpecificationSet::fromStorage(7, 'Phones', $status, new SpecificationSetStructure(1, $groups));
    }

    private function reader(?SpecificationSet $set): SpecificationsTableReader
    {
        $types = new SpecificationTypeRegistry(new TextType());
        $types->register(new TrueFalseType());

        $sets = new class ($set) implements SpecificationSetRepository {
            public function __construct(private readonly ?SpecificationSet $set)
            {
            }

            public function byId(int $id): ?SpecificationSet
            {
                return $this->set;
            }

            public function bySlug(string $slug): ?SpecificationSet
            {
                return $slug === 'phones' ? $this->set : null;
            }

            public function all(array $args = []): array
            {
                return [];
            }

            public function save(SpecificationSet $set): int
            {
                return 0;
            }

            public function delete(int $id): bool
            {
                return false;
            }
        };

        return new SpecificationsTableReader(
            new WordPressProductValueRepository(),
            $sets,
            new SpecificationDefinitionRepository(new SpecificationDefinitionMapper()),
            $types,
            new MigrationState(new DataVersionRepository())
        );
    }
}
