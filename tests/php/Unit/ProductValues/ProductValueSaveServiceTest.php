<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\ProductValues;

use Amiut\ProductSpecs\ProductValues\ProductValueDocument;
use Amiut\ProductSpecs\ProductValues\ProductValueRepository;
use Amiut\ProductSpecs\ProductValues\ProductValueSaveService;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionMapper;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use Amiut\ProductSpecs\Specification\Type\TextType;
use PHPUnit\Framework\TestCase;
use WP_Post;
use WP_Term;

final class ProductValueSaveServiceTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['wp_test_post_types'] = [20 => 'product'];
        $GLOBALS['wp_test_terms'] = [];
        $GLOBALS['wp_test_term_meta'] = [];
        $GLOBALS['wp_test_actions'] = [];

        $set = new WP_Post();
        $set->ID = 30;
        $set->post_type = 'specs-table';
        $set->post_name = 'laptops';
        $GLOBALS['wp_test_posts_by_id'] = [30 => $set];
    }

    public function testItSanitizesKnownValuesPreservesOrphansAndAnnouncesTheReplace(): void
    {
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

        $repository = $this->repository(
            ProductValueDocument::fromValues(['old-a1b2c3d4' => ['raw']])
        );
        $registry = new SpecificationTypeRegistry(new TextType());
        $service = new ProductValueSaveService(
            $repository,
            new SpecificationDefinitionRepository(new SpecificationDefinitionMapper()),
            $registry
        );

        $saved = $service->save(
            20,
            'laptops',
            [
                'screen-a1b2c3d4' => "  <b>15 inch</b>\n",
                'old-a1b2c3d4' => ['raw'],
                'empty-a1b2c3d4' => null,
            ]
        );

        self::assertSame(
            [
                'screen-a1b2c3d4' => '15 inch',
                'old-a1b2c3d4' => ['raw'],
                'empty-a1b2c3d4' => null,
            ],
            $saved
        );
        self::assertSame('laptops', $repository->assignedSet(20));
        self::assertSame($saved, $repository->read(20)->values());
        self::assertSame('product_specifications_values_saved', $GLOBALS['wp_test_actions'][0][0]);
    }

    public function testUnassignDeletesTheWholeValueMap(): void
    {
        $repository = $this->repository(
            ProductValueDocument::fromValues(['screen-a1b2c3d4' => '15'])
        );
        $service = new ProductValueSaveService(
            $repository,
            new SpecificationDefinitionRepository(new SpecificationDefinitionMapper()),
            new SpecificationTypeRegistry(new TextType())
        );

        self::assertSame([], $service->save(20, null, ['screen-a1b2c3d4' => '15']));
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

        $productService = new ProductValueSaveService($this->repository($empty), $definitions, $registry);
        self::assertSame([], $productService->save(20, 'laptops', ['screen-a1b2c3d4' => '   ']));

        $variationService = new ProductValueSaveService($this->repository($empty), $definitions, $registry);
        self::assertSame(
            ['screen-a1b2c3d4' => null],
            $variationService->save(21, 'laptops', ['screen-a1b2c3d4' => '   '])
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

            public function countContaining(string $specificationKey): int
            {
                return 0;
            }

            public function purge(string $specificationKey): bool
            {
                return true;
            }
        };
        $service = new ProductValueSaveService(
            $repository,
            new SpecificationDefinitionRepository(new SpecificationDefinitionMapper()),
            new SpecificationTypeRegistry(new TextType())
        );

        $this->expectException(\RuntimeException::class);

        try {
            $service->save(20, 'laptops', []);
        } finally {
            self::assertSame('old-set', $repository->assignedSet(20));
        }
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

            public function countContaining(string $specificationKey): int
            {
                return 0;
            }

            public function purge(string $specificationKey): bool
            {
                return true;
            }
        };
    }
}
