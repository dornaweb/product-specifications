<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\ImportExport;

use Amiut\ProductSpecs\ImportExport\EntryOutcome;
use Amiut\ProductSpecs\ImportExport\ImportPlan;
use Amiut\ProductSpecs\ImportExport\ProductMatcher;
use Amiut\ProductSpecs\ImportExport\Section\SpecificationsSection;
use Amiut\ProductSpecs\ImportExport\Section\ValuesSection;
use Amiut\ProductSpecs\ProductValues\ProductValueWriter;
use Amiut\ProductSpecs\ProductValues\WordPressProductValueRepository;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionMapper;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\Type\Document\Attachment;
use Amiut\ProductSpecs\Specification\Type\Document\DocumentType;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use Amiut\ProductSpecs\Specification\Type\TextType;
use Amiut\ProductSpecs\Specification\Type\TrueFalseType;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSet;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetStructure;
use Amiut\ProductSpecs\Tests\Unit\Specification\Type\Document\InMemoryAttachments;
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

    public function testAnExportLeavesOutValuesWhoseSpecificationIsGone(): void
    {
        Sections::storedSpecification(4, 'resolution', 'Resolution', 'pixels');
        $GLOBALS['wp_test_posts'] = [self::PHONE];
        $GLOBALS['wp_test_post_meta'][self::PHONE][WordPressProductValueRepository::VALUES_META_KEY]['values']
            += ['resolution' => ['width' => 1920]];

        $exported = [...$this->section()->export()];

        self::assertSame(
            ['weight' => '150 g', 'resolution' => ['width' => 1920]],
            (array) $exported[0]['values']
        );
    }

    public function testAFileIsExportedAsItsPathAndImportedAsTheAttachmentAtThatPath(): void
    {
        Sections::storedSpecification(6, 'manuals', 'Manuals', 'document');
        $GLOBALS['wp_test_posts'] = [self::PHONE];
        $GLOBALS['wp_test_post_meta'][self::PHONE][WordPressProductValueRepository::VALUES_META_KEY]['values']
            = ['manuals' => [['id' => 482, 'label' => 'Datasheet']]];

        $exported = [...$this->section()->export()];

        self::assertSame(
            ['manuals' => [['file' => '2026/11/x1.pdf', 'label' => 'Datasheet']]],
            (array) $exported[0]['values']
        );

        $GLOBALS['wp_test_post_meta'][self::PHONE][WordPressProductValueRepository::VALUES_META_KEY]['values'] = [];
        $this->section()->importEntry(
            [
                'object' => ['type' => 'product', 'sku' => 'PH-100'],
                'values' => ['manuals' => [['file' => '2026/11/x1.pdf', 'label' => 'Datasheet']]],
            ],
            new ImportPlan()
        );

        self::assertSame(
            ['manuals' => [['id' => 482, 'label' => 'Datasheet']]],
            $GLOBALS['wp_test_post_meta'][self::PHONE][WordPressProductValueRepository::VALUES_META_KEY]['values']
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
        $types->register(new DocumentType((new InMemoryAttachments())->with(
            new Attachment(482, 'Datasheet X1', 'https://shop.test/uploads/2026/11/x1.pdf', '2026/11/x1.pdf', null)
        )));

        $definitions = new SpecificationDefinitionRepository(new SpecificationDefinitionMapper());
        $sets = new InMemorySetRepository([
            'phones' => SpecificationSet::fromStorage(
                5,
                'Phones',
                'publish',
                SpecificationSetStructure::empty(),
                'phones'
            ),
        ]);
        $values = new WordPressProductValueRepository();

        return new ValuesSection(
            $values,
            new ProductValueWriter($values, $definitions, $types, $sets),
            $definitions,
            $sets,
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
