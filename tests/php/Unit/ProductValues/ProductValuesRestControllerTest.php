<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\ProductValues;

use Amiut\ProductSpecs\ProductValues\ProductValuesRestController;
use Amiut\ProductSpecs\ProductValues\ProductValueWriter;
use Amiut\ProductSpecs\ProductValues\WordPressProductValueRepository;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionMapper;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\Type\SelectType;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use Amiut\ProductSpecs\Specification\Type\TextType;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetMapper;
use Amiut\ProductSpecs\SpecificationSet\WordPressSpecificationSetRepository;
use PHPUnit\Framework\TestCase;
use WP_Error;
use WP_Post;
use WP_REST_Request;
use WP_Term;

final class ProductValuesRestControllerTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['wp_test_post_types'] = [20 => 'product'];
        $GLOBALS['wp_test_post_meta'] = [];
        $GLOBALS['wp_test_actions'] = [];

        $set = new WP_Post();
        $set->ID = 30;
        $set->post_type = 'specs-table';
        $set->post_name = 'laptops';
        $GLOBALS['wp_test_posts_by_id'] = [30 => $set];

        $term = new WP_Term();
        $term->term_id = 11;
        $term->name = 'Colour';
        $term->slug = 'colour-a1b2c3d4';
        $GLOBALS['wp_test_terms'] = [11 => $term];
        $GLOBALS['wp_test_term_meta'] = [
            11 => ['attr_type' => 'select', 'attr_values' => ['values' => ['Red']], 'attr_default' => ''],
        ];
    }

    public function testARejectedValueIsA400NamingEachSpecification(): void
    {
        $response = $this->controller()->update(
            new WP_REST_Request(['id' => 20, 'set' => 'laptops', 'values' => ['colour-a1b2c3d4' => 'Green']])
        );

        self::assertInstanceOf(WP_Error::class, $response);
        self::assertSame(
            ['status' => 400, 'errors' => ['colour-a1b2c3d4' => 'Choose one of the options.']],
            $response->get_error_data()
        );
    }

    private function controller(): ProductValuesRestController
    {
        $types = new SpecificationTypeRegistry(new TextType());
        $types->register(new SelectType());
        $values = new WordPressProductValueRepository();

        return new ProductValuesRestController(
            $values,
            new ProductValueWriter(
                $values,
                new SpecificationDefinitionRepository(new SpecificationDefinitionMapper()),
                $types,
                new WordPressSpecificationSetRepository(new SpecificationSetMapper())
            )
        );
    }
}
