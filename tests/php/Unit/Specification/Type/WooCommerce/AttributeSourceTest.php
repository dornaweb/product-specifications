<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Type\WooCommerce;

use Amiut\ProductSpecs\Specification\Type\WooCommerce\AttributeSource;
use PHPUnit\Framework\TestCase;

final class AttributeSourceTest extends TestCase
{
    protected function tearDown(): void
    {
        $GLOBALS['wc_test_products'] = [];
    }

    public function testAVariationReadsTheParentsAttributeRatherThanItsOwnOption(): void
    {
        $GLOBALS['wc_test_products'][10] = new FakeProduct(10, [], false, ['pa_color' => 'Red, Blue']);

        $value = (new AttributeSource('pa_color'))->value(new FakeVariation(11, 10, ['pa_color' => 'Red']));

        self::assertSame('Red, Blue', $value);
    }

    public function testACustomAttributesOptionsAreSeparatedByCommas(): void
    {
        $product = new FakeProduct(10, [], false, ['material' => 'Cotton | Linen &amp; Silk']);

        self::assertSame('Cotton, Linen & Silk', (new AttributeSource('material'))->value($product));
    }
}
