<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Type\Unit;

use Amiut\ProductSpecs\Specification\Type\Unit\Measurement;
use PHPUnit\Framework\TestCase;

final class MeasurementTest extends TestCase
{
    /**
     * @return array<string, array{mixed, array<string, mixed>|null}>
     */
    public static function readings(): array
    {
        return [
            'a number with its unit' => [['value' => 6.1, 'unit' => 'in'], ['value' => 6.1, 'unit' => 'in']],
            'a decimal comma' => [['value' => '6,1', 'unit' => 'in'], ['value' => 6.1, 'unit' => 'in']],
            'a trailing separator' => [['value' => '6,', 'unit' => 'in'], ['value' => 6, 'unit' => 'in']],
            'no unit takes the default' => [['value' => '15'], ['value' => 15, 'unit' => 'cm']],
            'a range' => [['min' => '100', 'max' => 240, 'unit' => 'V'], ['min' => 100, 'max' => 240, 'unit' => 'V']],
            'a range with equal ends' => [['min' => 5, 'max' => 5, 'unit' => 'V'], ['value' => 5, 'unit' => 'V']],
            'a range with one end' => [['min' => '', 'max' => '240', 'unit' => 'V'], ['value' => 240, 'unit' => 'V']],
            'a unit with markup' => [['value' => 5, 'unit' => '<b>kg</b>'], ['value' => 5, 'unit' => 'kg']],
            'text is not read' => ['180 g', null],
            'a bare number is not read' => [180, null],
            'not a number' => [['value' => 'six', 'unit' => 'in'], null],
            'a thousands separator is not a number' => [['value' => '1.000,5'], null],
        ];
    }

    /**
     * @dataProvider readings
     * @param array<string, mixed>|null $expected
     */
    public function testInputAndStoredDataAreReadAsANumberAndAUnit(mixed $data, ?array $expected): void
    {
        self::assertSame($expected, Measurement::fromData($data, 'cm')?->toStorage());
    }

    public function testRoundingKeepsTheAllowedDecimalsAndCanCollapseARange(): void
    {
        self::assertSame(
            ['value' => 6.13, 'unit' => 'in'],
            Measurement::fromData(['value' => '6.125'], 'in')?->rounded(2)->toStorage()
        );
        self::assertSame(
            ['value' => 2, 'unit' => 'kg'],
            Measurement::fromData(['min' => 1.6, 'max' => 2.4], 'kg')?->rounded(0)->toStorage()
        );
    }
}
