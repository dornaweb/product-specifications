<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\ProductValues;

use Amiut\ProductSpecs\ProductValues\ProductValueDocument;
use Amiut\ProductSpecs\ProductValues\ProductValueRepository;
use Amiut\ProductSpecs\ProductValues\ProductValueWriter;
use Amiut\ProductSpecs\ProductValues\RejectedValues;
use Amiut\ProductSpecs\ProductValues\WordPressProductValueRepository;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionMapper;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\Type\SelectType;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use Amiut\ProductSpecs\Specification\Type\TextType;
use Amiut\ProductSpecs\Specification\Type\TrueFalseType;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetMapper;
use Amiut\ProductSpecs\SpecificationSet\WordPressSpecificationSetRepository;
use PHPUnit\Framework\TestCase;
use WP_Post;
use WP_Term;

final class ProductValueWriterTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['wp_test_post_types'] = [20 => 'product'];
        $GLOBALS['wp_test_terms'] = [];
        $GLOBALS['wp_test_term_meta'] = [];
        $GLOBALS['wp_test_actions'] = [];
        $GLOBALS['wp_test_post_meta'] = [];
        $GLOBALS['wp_test_posts'] = [];

        $set = new WP_Post();
        $set->ID = 30;
        $set->post_type = 'specs-table';
        $set->post_name = 'laptops';
        $GLOBALS['wp_test_posts_by_id'] = [30 => $set];
    }

    public function testAReplaceKeepsValuesOfAnInactiveTypeAndDropsDeletedSpecifications(): void
    {
        $this->storeSpecification(10, 'screen-a1b2c3d4', 'text');
        $this->storeSpecification(11, 'old-a1b2c3d4', 'pixels');

        $repository = $this->repository(
            ProductValueDocument::fromValues(['old-a1b2c3d4' => ['raw']])
        );
        $registry = new SpecificationTypeRegistry(new TextType());
        $service = new ProductValueWriter(
            $repository,
            new SpecificationDefinitionRepository(new SpecificationDefinitionMapper()),
            $registry,
            new WordPressSpecificationSetRepository(new SpecificationSetMapper())
        );

        $saved = $service->replace(
            20,
            'laptops',
            [
                'screen-a1b2c3d4' => "  <b>15 inch</b>\n",
                'old-a1b2c3d4' => ['raw'],
                'deleted-a1b2c3d4' => 'Red',
            ]
        );

        self::assertSame(
            [
                'screen-a1b2c3d4' => '15 inch',
                'old-a1b2c3d4' => ['raw'],
            ],
            $saved
        );
        self::assertSame('laptops', $repository->assignedSet(20));
        self::assertSame($saved, $repository->read(20)->values());
        self::assertSame(ProductValueWriter::SAVED_ACTION, $GLOBALS['wp_test_actions'][0][0]);
    }

    public function testUnassignDeletesTheWholeValueMap(): void
    {
        $repository = $this->repository(
            ProductValueDocument::fromValues(['screen-a1b2c3d4' => '15'])
        );
        $service = new ProductValueWriter(
            $repository,
            new SpecificationDefinitionRepository(new SpecificationDefinitionMapper()),
            new SpecificationTypeRegistry(new TextType()),
            new WordPressSpecificationSetRepository(new SpecificationSetMapper())
        );

        self::assertSame([], $service->replace(20, null, ['screen-a1b2c3d4' => '15']));
        self::assertNull($repository->assignedSet(20));
        self::assertSame([], $repository->read(20)->values());
    }

    public function testAKnownValueSanitizingToNullIsDroppedForAProductButKeptForAVariation(): void
    {
        $GLOBALS['wp_test_post_types'][21] = 'product_variation';

        $term = new WP_Term();
        $term->term_id = 10;
        $term->name = 'Screen';
        $term->slug = 'screen-a1b2c3d4';
        $GLOBALS['wp_test_terms'][10] = $term;
        $GLOBALS['wp_test_term_meta'][10] = [
            'attr_type' => 'text',
            'attr_values' => [],
            'attr_default' => '',
        ];

        $definitions = new SpecificationDefinitionRepository(new SpecificationDefinitionMapper());
        $registry = new SpecificationTypeRegistry(new TextType());
        $empty = ProductValueDocument::empty();

        $productService = new ProductValueWriter(
            $this->repository($empty),
            $definitions,
            $registry,
            $this->sets()
        );
        self::assertSame([], $productService->replace(20, 'laptops', ['screen-a1b2c3d4' => '   ']));

        $variationService = new ProductValueWriter(
            $this->repository($empty),
            $definitions,
            $registry,
            $this->sets()
        );
        self::assertSame(
            ['screen-a1b2c3d4' => null],
            $variationService->replace(21, 'laptops', ['screen-a1b2c3d4' => '   '])
        );
    }

    public function testAFailedWriteRevertsTheSetAssignmentAndSurfacesAnError(): void
    {
        $repository = new class (ProductValueDocument::empty()) implements ProductValueRepository {
            private ?string $set = 'old-set';

            public function __construct(private ProductValueDocument $document)
            {
            }

            public function read(int $postId): ProductValueDocument
            {
                return $this->document;
            }

            public function replace(int $postId, ProductValueDocument $document): bool
            {
                return false;
            }

            public function delete(int $postId): bool
            {
                return true;
            }

            public function assignedSet(int $postId): ?string
            {
                return $this->set;
            }

            public function assignSet(int $postId, ?string $setSlug): bool
            {
                $this->set = $setSlug;

                return true;
            }

            public function countUsing(string $specificationKey, array $values): int
            {
                return 0;
            }
        };
        $service = new ProductValueWriter(
            $repository,
            new SpecificationDefinitionRepository(new SpecificationDefinitionMapper()),
            new SpecificationTypeRegistry(new TextType()),
            new WordPressSpecificationSetRepository(new SpecificationSetMapper())
        );

        $this->expectException(\RuntimeException::class);

        try {
            $service->replace(20, 'laptops', []);
        } finally {
            self::assertSame('old-set', $repository->assignedSet(20));
        }
    }

    public function testAMergeLaysAcceptedValuesOverTheStoredOnesAndAnnouncesTheChange(): void
    {
        $this->storeSpecification(10, 'screen-a1b2c3d4', 'text');
        $this->storeSpecification(11, 'wifi-a1b2c3d4', 'true_false');
        $GLOBALS['wp_test_post_meta'][20] = [
            WordPressProductValueRepository::VALUES_META_KEY => [
                'schema_version' => 1,
                'values' => ['screen-a1b2c3d4' => '13"', 'colour-a1b2c3d4' => 'Blue'],
            ],
        ];

        $merged = $this->writer()->merge(
            20,
            'laptops',
            ['screen-a1b2c3d4' => '16"', 'wifi-a1b2c3d4' => 'maybe', 'ghost-a1b2c3d4' => '1']
        );

        self::assertSame(['screen-a1b2c3d4' => '16"', 'colour-a1b2c3d4' => 'Blue'], $merged);
        self::assertSame(
            'laptops',
            $GLOBALS['wp_test_post_meta'][20][WordPressProductValueRepository::SET_META_KEY]
        );
        self::assertSame(
            [
                ProductValueWriter::SAVED_ACTION,
                [20, $merged, ['screen-a1b2c3d4' => '13"', 'colour-a1b2c3d4' => 'Blue']],
            ],
            $GLOBALS['wp_test_actions'][0]
        );
    }

    public function testAMergeThatChangesNoValueAnnouncesNothing(): void
    {
        $this->storeSpecification(10, 'screen-a1b2c3d4', 'text');
        $GLOBALS['wp_test_post_meta'][20] = [
            WordPressProductValueRepository::VALUES_META_KEY => [
                'schema_version' => 1,
                'values' => ['screen-a1b2c3d4' => '13"'],
            ],
        ];

        $this->writer()->merge(20, null, ['screen-a1b2c3d4' => '13"']);

        self::assertSame([], $GLOBALS['wp_test_actions']);
    }

    public function testAChangedValueItsTypeRejectsFailsTheWholeSave(): void
    {
        $this->storeSpecification(10, 'screen-a1b2c3d4', 'text');
        $this->storeSpecification(11, 'colour-a1b2c3d4', 'select', ['Red', 'Blue']);
        $this->storeValues(['screen-a1b2c3d4' => '13"']);

        try {
            $this->writer()->replace(20, 'laptops', ['screen-a1b2c3d4' => '15"', 'colour-a1b2c3d4' => 'Green']);
            self::fail('Expected the save to be rejected.');
        } catch (RejectedValues $rejected) {
            self::assertSame(['colour-a1b2c3d4' => 'Choose one of the options.'], $rejected->messages());
        }

        self::assertSame(
            ['screen-a1b2c3d4' => '13"'],
            $GLOBALS['wp_test_post_meta'][20][WordPressProductValueRepository::VALUES_META_KEY]['values']
        );
        self::assertArrayNotHasKey(WordPressProductValueRepository::SET_META_KEY, $GLOBALS['wp_test_post_meta'][20]);
        self::assertSame([], $GLOBALS['wp_test_actions']);
    }

    public function testAStoredValueItsTypeRejectsDoesNotBlockTheSave(): void
    {
        $this->storeSpecification(11, 'colour-a1b2c3d4', 'select', ['Red', 'Blue']);
        $this->storeValues(['colour-a1b2c3d4' => 'Green']);

        self::assertSame(
            ['colour-a1b2c3d4' => 'Green'],
            $this->writer()->replace(20, 'laptops', ['colour-a1b2c3d4' => 'Green'])
        );
    }

    public function testTheSpecificationsOwnDefaultIsNeverRejected(): void
    {
        $this->storeSpecification(11, 'colour-a1b2c3d4', 'select', ['Red', 'Blue']);
        $GLOBALS['wp_test_term_meta'][11]['attr_default'] = 'Green';

        self::assertSame(
            ['colour-a1b2c3d4' => 'Green'],
            $this->writer()->replace(20, 'laptops', ['colour-a1b2c3d4' => 'Green'])
        );
    }

    /**
     * @param array<string, mixed> $values
     */
    private function storeValues(array $values): void
    {
        $GLOBALS['wp_test_post_meta'][20] = [
            WordPressProductValueRepository::VALUES_META_KEY => ['schema_version' => 1, 'values' => $values],
        ];
    }

    private function writer(): ProductValueWriter
    {
        $types = new SpecificationTypeRegistry(new TextType());
        $types->register(new TrueFalseType());
        $types->register(new SelectType());

        return new ProductValueWriter(
            new WordPressProductValueRepository(),
            new SpecificationDefinitionRepository(new SpecificationDefinitionMapper()),
            $types,
            new WordPressSpecificationSetRepository(new SpecificationSetMapper())
        );
    }

    /**
     * @param list<string> $choices
     */
    private function storeSpecification(int $id, string $slug, string $type, array $choices = []): void
    {
        $term = new WP_Term();
        $term->term_id = $id;
        $term->name = $slug;
        $term->slug = $slug;
        $GLOBALS['wp_test_terms'][$id] = $term;
        $GLOBALS['wp_test_term_meta'][$id] = [
            'attr_type' => $type,
            'attr_values' => $choices === [] ? [] : ['values' => $choices],
            'attr_default' => '',
        ];
    }

    private function repository(ProductValueDocument $initial): ProductValueRepository
    {
        return new class ($initial) implements ProductValueRepository {
            private ?string $set = 'old-set';

            public function __construct(private ProductValueDocument $document)
            {
            }

            public function read(int $postId): ProductValueDocument
            {
                return $this->document;
            }

            public function replace(int $postId, ProductValueDocument $document): bool
            {
                $this->document = $document;

                return true;
            }

            public function delete(int $postId): bool
            {
                $this->document = ProductValueDocument::empty();

                return true;
            }

            public function assignedSet(int $postId): ?string
            {
                return $this->set;
            }

            public function assignSet(int $postId, ?string $setSlug): bool
            {
                $this->set = $setSlug;

                return true;
            }

            public function countUsing(string $specificationKey, array $values): int
            {
                return 0;
            }
        };
    }

    private function sets(): WordPressSpecificationSetRepository
    {
        return new WordPressSpecificationSetRepository(new SpecificationSetMapper());
    }
}
