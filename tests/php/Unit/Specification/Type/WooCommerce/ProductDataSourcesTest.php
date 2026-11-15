<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Type\WooCommerce;

use Amiut\ProductSpecs\Specification\Type\WooCommerce\ProductDataSources;
use PHPUnit\Framework\TestCase;
use WP_Taxonomy;

final class ProductDataSourcesTest extends TestCase
{
    protected function tearDown(): void
    {
        $GLOBALS['wp_test_product_taxonomies'] = [];
        $GLOBALS['wc_test_attribute_taxonomies'] = [];
    }

    public function testOffersTheSitesProductTaxonomiesAndAttributesButNotInternalOrAttributeTaxonomies(): void
    {
        $GLOBALS['wp_test_product_taxonomies'] = [
            'product_type' => new WP_Taxonomy('product_type', false, 'Product types'),
            'product_cat' => new WP_Taxonomy('product_cat', true, 'Categories'),
            'product_brand' => new WP_Taxonomy('product_brand', true, 'Brands'),
            'pa_color' => new WP_Taxonomy('pa_color', true, 'Color'),
        ];
        $GLOBALS['wc_test_attribute_taxonomies'] = [
            'id:1' => (object) ['attribute_name' => 'color', 'attribute_label' => 'Color'],
        ];

        $offered = array_values(array_filter(
            (new ProductDataSources())->choices(),
            static fn (array $choice): bool => $choice['name'] !== ''
        ));

        self::assertSame(
            [
                ['source' => 'taxonomy', 'name' => 'product_cat', 'label' => 'Categories', 'group' => 'taxonomies'],
                ['source' => 'taxonomy', 'name' => 'product_brand', 'label' => 'Brands', 'group' => 'taxonomies'],
                ['source' => 'attribute', 'name' => 'pa_color', 'label' => 'Color', 'group' => 'attributes'],
            ],
            $offered
        );
    }
}
