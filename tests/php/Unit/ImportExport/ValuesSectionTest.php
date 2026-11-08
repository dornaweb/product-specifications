<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\ImportExport;

use Amiut\ProductSpecs\ImportExport\EntryOutcome;
use Amiut\ProductSpecs\ImportExport\ImportPlan;
use Amiut\ProductSpecs\ImportExport\ProductMatcher;
use Amiut\ProductSpecs\ImportExport\Section\SpecificationsSection;
use Amiut\ProductSpecs\ImportExport\Section\ValuesSection;
use Amiut\ProductSpecs\ProductValues\WordPressProductValueRepository;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionMapper;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use Amiut\ProductSpecs\Specification\Type\TextType;
use Amiut\ProductSpecs\Specification\Type\TrueFalseType;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSet;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetStructure;
use PHPUnit\Framework\TestCase;
use WP_Post;

final class ValuesSectionTest extends TestCase
{
    private const PHONE = 20;
    private const OTHER_PHONE = 30;

    protected function setUp(): void
    {
        $GLOBALS['wp_test_terms'] = [];
        $GLOBALS['wp_test_term_meta'] = [];
        $GLOBALS['wp_test_post_meta'] = [];
        $GLOBALS['wp_test_post_types'] = [self::PHONE => 'product', self::OTHER_PHONE => 'product'];
        $GLOBALS['wp_test_skus'] = ['PH-100' => self::PHONE];
        $GLOBALS['wp_test_posts_by_id'] = [self::OTHER_PHONE => $this->post(self::OTHER_PHONE, 'phone-200')];

        Sections::storedSpecification(1, 'weight', 'Weight', 'text');
        Sections::storedSpecification(2, 'wifi', 'Wi-Fi', 'true_false');
        Sections::storedSpecification(3, 'battery', 'Battery', 'text');

        $GLOBALS['wp_test_post_meta'][self::PHONE] = [
            WordPressProductValueRepository::SET_META_KEY => 'old-set',
            WordPressProductValueRepository::VALUES_META_KEY => [
                'schema_version' => 1,
                'values' => ['weight' => '150 g', 'colour' => 'Blue'],
            ],
        ];
    }

    public function testEachMalformedEntryIsReportedWithItsPath(): void
    {
        self::assertSame(
            [
                0 => ['[0].object: needs a sku or a slug'],
                1 => ['[1].values: must be an object'],
                2 => ['[2].values.Bad Key: is not a specification slug'],
                4 => ['[4].object: is a duplicate'],
            ],
            $this->section()->validate([
                ['object' => ['type' => 'product'], 'values' => []],
                ['object' => ['type' => 'product', 'sku' => 'A'], 'values' => ['x']],
                ['object' => ['type' => 'product', 'sku' => 'B'], 'values' => ['Bad Key' => 'x']],
                ['object' => ['type' => 'product', 'sku' => 'C'], 'values' => []],
                ['object' => ['type' => 'product', 'slug' => 'c', 'sku' => 'C'], 'values' => []],
            ])
        );
    }

    public function testASkuIsPreferredAndASlugIsTheFallback(): void
    {
        $matcher = new ProductMatcher();

        self::assertSame(self::PHONE, $matcher->find(['sku' => 'PH-100', 'slug' => 'phone-200']));
        self::assertSame(self::OTHER_PHONE, $matcher->find(['sku' => 'UNKNOWN', 'slug' => 'phone-200']));
        self::assertNull($matcher->find(['sku' => 'UNKNOWN', 'slug' => 'nothing']));
    }

    public function testAnUnmatchedProductIsSkipped(): void
    {
        $outcome = $this->section()->planEntry($this->entry(['sku' => 'NONE']), $this->plan());

        self::assertSame([EntryOutcome::SKIP, 'SKU NONE'], [$outcome->status(), $outcome->label()]);
    }

    public function testValuesAreMergedAndWhatCannotBeImportedIsExplained(): void
    {
        $section = $this->section();
        $entry = $this->entry(['sku' => 'PH-100']);
        $plan = $this->plan();

        $planned = $section->planEntry($entry, $plan);

        self::assertSame(EntryOutcome::UPDATE, $planned->status());
        self::assertSame(
            [
                'Skips the value for "battery": the file\'s specification is not imported.',
                'Skips the value for "ghost": no specification has that slug.',
            ],
            $planned->notes()
        );

        $section->importEntry($entry, $plan);

        self::assertSame(
            ['weight' => '180 g', 'colour' => 'Blue', 'wifi' => true],
            $GLOBALS['wp_test_post_meta'][self::PHONE][WordPressProductValueRepository::VALUES_META_KEY]['values']
        );
        self::assertSame(
            'phones',
            $GLOBALS['wp_test_post_meta'][self::PHONE][WordPressProductValueRepository::SET_META_KEY]
        );
    }

    /**
     * @param array<string, string> $identity
     * @return array<string, mixed>
     */
    private function entry(array $identity): array
    {
        return [
            'object' => ['type' => 'product'] + $identity,
            'set' => 'phones',
            'values' => ['weight' => '180 g', 'wifi' => 'yes', 'battery' => '5000 mAh', 'ghost' => '1'],
        ];
    }

    private function plan(): ImportPlan
    {
        $plan = new ImportPlan();
        $plan->record(SpecificationsSection::KEY, 0, EntryOutcome::conflict('battery', 'type differs'));

        return $plan;
    }

    private function section(): ValuesSection
    {
        $types = new SpecificationTypeRegistry(new TextType());
        $types->register(new TrueFalseType());

        return new ValuesSection(
            new WordPressProductValueRepository(),
            new SpecificationDefinitionRepository(new SpecificationDefinitionMapper()),
            new InMemorySetRepository([
                'phones' => SpecificationSet::fromStorage(
                    5,
                    'Phones',
                    'publish',
                    SpecificationSetStructure::empty(),
                    'phones'
                ),
            ]),
            $types,
            [new ProductMatcher()]
        );
    }

    private function post(int $id, string $slug): WP_Post
    {
        $post = new WP_Post();
        $post->ID = $id;
        $post->post_type = 'product';
        $post->post_name = $slug;

        return $post;
    }
}
