<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Type;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use Amiut\ProductSpecs\Specification\Type\TrueFalseType;
use PHPUnit\Framework\TestCase;

final class TrueFalseTypeTest extends TestCase
{
    public function testThePlainValueIsTheWordEvenWhenTheSpecificationShowsIcons(): void
    {
        $type = new TrueFalseType();
        $icons = SpecificationOptions::fromMap(['display' => 'icons', 'labels' => 'Included|Not included']);

        self::assertSame('Included', $type->plainValue(true, $icons));
        self::assertSame('Not included', $type->plainValue('no', $icons));
        self::assertSame('', $type->plainValue(null, $icons));
    }
}
