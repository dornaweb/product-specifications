<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use PHPUnit\Framework\TestCase;

final class SpecificationOptionsTest extends TestCase
{
    /**
     * @return array<string, array{mixed}>
     */
    public static function storedChoices(): array
    {
        return [
            'map' => [['values' => ['5.5"', '6.1"']]],
            'map with empty entries' => [['values' => ['5.5"', '', '6.1"']]],
            'choices with gaps in their keys' => [['values' => [0 => '5.5"', 2 => '6.1"']]],
        ];
    }

    /**
     * @dataProvider storedChoices
     */
    public function testStoredChoicesReadAsAGaplessListWithoutEmptyEntries(mixed $stored): void
    {
        static::assertSame(['5.5"', '6.1"'], SpecificationOptions::fromStorage($stored)->choices());
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
            'list without option names' => [['5.5"', '6.1"']],
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
