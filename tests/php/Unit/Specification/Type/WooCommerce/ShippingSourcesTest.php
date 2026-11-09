<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Type\WooCommerce;

use Amiut\ProductSpecs\Specification\Type\WooCommerce\Dimension;
use Amiut\ProductSpecs\Specification\Type\WooCommerce\DimensionSource;
use Amiut\ProductSpecs\Specification\Type\WooCommerce\DimensionsSource;
use Amiut\ProductSpecs\Specification\Type\WooCommerce\ProductDataSource;
use Amiut\ProductSpecs\Specification\Type\WooCommerce\WeightSource;
use PHPUnit\Framework\TestCase;

final class ShippingSourcesTest extends TestCase
{
    /**
     * WooCommerce's formatters answer an empty field with "N/A", which would make the row
     * never empty.
     *
     * @return array<string, array{ProductDataSource, FakeProduct}>
     */
    public static function emptyFields(): array
    {
        return [
            'no weight' => [new WeightSource(), new FakeProduct()],
            'a weight on a virtual product' => [new WeightSource(), new FakeProduct(1, ['weight' => '2'], true)],
            'no dimensions' => [new DimensionsSource(), new FakeProduct()],
            'no length' => [new DimensionSource(Dimension::Length), new FakeProduct(1, ['width' => '4'])],
            'a zero height' => [new DimensionSource(Dimension::Height), new FakeProduct(1, ['height' => '0'])],
            'a width on a virtual product' => [
                new DimensionSource(Dimension::Width),
                new FakeProduct(1, ['width' => '4'], true),
            ],
        ];
    }

    /**
     * @dataProvider emptyFields
     */
    public function testAnEmptyFieldReadsAsNothing(ProductDataSource $source, FakeProduct $product): void
    {
        self::assertNull($source->value($product));
    }

    public function testAValueIsFormattedByWooCommerceAsPlainText(): void
    {
        $product = new FakeProduct(1, ['weight' => '2.50', 'length' => '10', 'width' => '5', 'height' => '2']);

        self::assertSame('2.50 kg', (new WeightSource())->value($product));
        self::assertSame('10 × 5 × 2 cm', (new DimensionsSource())->value($product));
        self::assertSame('5 cm', (new DimensionSource(Dimension::Width))->value($product));
    }
}
