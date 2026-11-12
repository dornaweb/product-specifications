<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Type\WooCommerce;

use Amiut\ProductSpecs\Specification\Type\WooCommerce\TaxonomySource;
use PHPUnit\Framework\TestCase;
use WP_Error;
use WP_Term;

final class TaxonomySourceTest extends TestCase
{
    private const PARENT = 10;
    private const VARIATION = 11;

    protected function tearDown(): void
    {
        $GLOBALS['wp_test_object_terms'] = [];
        $GLOBALS['wc_test_products'] = [];
    }

    public function testAVariationReadsItsParentsTerms(): void
    {
        $GLOBALS['wc_test_products'][self::PARENT] = new FakeProduct(self::PARENT);
        $GLOBALS['wp_test_object_terms'][self::PARENT]['product_brand'] = [self::term('Acme', 'acme')];

        $value = (new TaxonomySource('product_brand'))->value(new FakeVariation(self::VARIATION, self::PARENT));

        self::assertSame([['name' => 'Acme', 'url' => 'https://example.test/term/acme']], $value);
    }

    public function testTermsRenderAsEscapedLinksAndReadAsPlainNames(): void
    {
        $GLOBALS['wp_test_object_terms'][self::PARENT]['product_cat'] = [
            self::term('Shirts &amp; Tops', 'shirts'),
            self::term('<b>Sale</b>', 'sale'),
        ];
        $source = new TaxonomySource('product_cat');
        $value = $source->value(new FakeProduct(self::PARENT));

        self::assertSame('Shirts & Tops, <b>Sale</b>', $source->plainValue($value));
        self::assertSame(
            '<a href="https://example.test/term/shirts" rel="tag">Shirts &amp; Tops</a>, '
                . '<a href="https://example.test/term/sale" rel="tag">&lt;b&gt;Sale&lt;/b&gt;</a>',
            $source->render($value)
        );
    }

    public function testNoTermsOrAnUnregisteredTaxonomyReadsAsNothing(): void
    {
        $GLOBALS['wp_test_object_terms'][self::PARENT]['product_tag'] = [];
        $GLOBALS['wp_test_object_terms'][self::PARENT]['gone'] = new WP_Error('invalid_taxonomy', 'Invalid taxonomy.');

        self::assertNull((new TaxonomySource('product_tag'))->value(new FakeProduct(self::PARENT)));
        self::assertNull((new TaxonomySource('gone'))->value(new FakeProduct(self::PARENT)));
    }

    private static function term(string $name, string $slug): WP_Term
    {
        $term = new WP_Term();
        $term->name = $name;
        $term->slug = $slug;

        return $term;
    }
}
