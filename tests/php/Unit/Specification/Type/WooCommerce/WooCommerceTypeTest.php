<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Type\WooCommerce;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use Amiut\ProductSpecs\Specification\Type\WooCommerce\ProductDataSources;
use Amiut\ProductSpecs\Specification\Type\WooCommerce\WooCommerceType;
use PHPUnit\Framework\TestCase;

final class WooCommerceTypeTest extends TestCase
{
    public function testAValueStoredUnderAnEarlierTypeSurvivesAProductSave(): void
    {
        $type = new WooCommerceType(new ProductDataSources());
        $options = SpecificationOptions::fromMap(['source' => 'weight']);

        self::assertSame('180 g', $type->sanitize('180 g', $options));
        self::assertSame(['value' => 6.1, 'unit' => 'in'], $type->sanitize(['value' => 6.1, 'unit' => 'in'], $options));
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public static function unresolvableOptions(): array
    {
        return [
            'no source' => [[]],
            'an unknown source' => [['source' => 'price']],
            'a taxonomy without a name' => [['source' => 'taxonomy']],
            'an attribute with a blank name' => [['source' => 'attribute', 'name' => '  ']],
        ];
    }

    /**
     * @dataProvider unresolvableOptions
     * @param array<string, mixed> $options
     */
    public function testOptionsNamingNoUsableSourceReadAsNothing(array $options): void
    {
        $type = new WooCommerceType(new ProductDataSources());
        $options = SpecificationOptions::fromMap($options);
        $product = new FakeProduct(1, ['weight' => '2']);

        self::assertNull($type->value($product, '180 g', $options));
        self::assertSame('', $type->render('180 g', $options));
        self::assertSame('', $type->plainValue('180 g', $options));
    }
}
