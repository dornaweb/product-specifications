<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use PHPUnit\Framework\TestCase;

/**
 * Options are stored as a bare list of choices or as a map, and nothing rewrites
 * a stored value, so both shapes have to keep reading the same. This is the one
 * place that is true.
 */
final class SpecificationOptionsTest extends TestCase
{
    /**
     * @return array<string, array{mixed}>
     */
    public static function equivalentStoredShapes(): array
    {
        return [
            'bare list' => [['5.5"', '6.1"']],
            'map' => [['values' => ['5.5"', '6.1"']]],
            'list with empty entries' => [['5.5"', '', '6.1"']],
            'map with empty entries' => [['values' => ['5.5"', '', '6.1"']]],
            'list with gaps in its keys' => [[0 => '5.5"', 2 => '6.1"']],
        ];
    }

    /**
     * @dataProvider equivalentStoredShapes
     */
    public function testEveryStoredShapeReadsTheSameChoices(mixed $stored): void
    {
        static::assertSame(['5.5"', '6.1"'], SpecificationOptions::fromStorage($stored)->choices());
    }

    public function testAStoredListIsNormalizedToTheMapOnTheWayOut(): void
    {
        $options = SpecificationOptions::fromStorage(['5.5"', '6.1"']);

        static::assertSame(['values' => ['5.5"', '6.1"']], $options->toArray());
    }

    public function testOptionsBelongingToOtherTypesSurviveUntouched(): void
    {
        $options = SpecificationOptions::fromStorage(['units' => ['mm', 'cm'], 'default_unit' => 'mm']);

        static::assertSame(['units' => ['mm', 'cm'], 'default_unit' => 'mm'], $options->toArray());
        static::assertSame([], $options->choices());
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function unusableStoredValues(): array
    {
        return [
            'never set' => [''],
            'false' => [false],
            'empty list' => [[]],
        ];
    }

    /**
     * @dataProvider unusableStoredValues
     */
    public function testAnUnusableStoredValueReadsAsEmpty(mixed $stored): void
    {
        $options = SpecificationOptions::fromStorage($stored);

        static::assertTrue($options->isEmpty());
        static::assertSame([], $options->choices());
    }
}
