<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Type\Unit;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use Amiut\ProductSpecs\Specification\Type\Unit\UnitType;
use PHPUnit\Framework\TestCase;
use WP_Locale;

final class UnitTypeTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['wp_locale']);
    }

    /**
     * @return array<string, array{mixed, string|null}>
     */
    public static function inputs(): array
    {
        return [
            'empty' => [['value' => '', 'unit' => 'V'], null],
            'within the bounds' => [['value' => '230', 'unit' => 'V'], null],
            'a range within the bounds' => [['min' => 100, 'max' => 240, 'unit' => 'V'], null],
            'not a number' => [['value' => 'high', 'unit' => 'V'], 'Enter a number.'],
            'a unit not allowed' => [['value' => 230, 'unit' => 'A'], 'Choose one of the units.'],
            'a reversed range' => [
                ['min' => 240, 'max' => 100, 'unit' => 'V'],
                'The first number cannot be larger than the second.',
            ],
            'below the minimum' => [['value' => 50, 'unit' => 'V'], 'Enter a number from 100 to 400.'],
            'a range above the maximum' => [
                ['min' => 100, 'max' => 500, 'unit' => 'V'],
                'Enter a number from 100 to 400.',
            ],
        ];
    }

    /**
     * @dataProvider inputs
     */
    public function testAValueIsRejectedForWhatItsOptionsDoNotAllow(mixed $input, ?string $rejection): void
    {
        self::assertSame($rejection, (new UnitType())->validate($input, $this->voltage(range: true)));
    }

    public function testARangeIsRejectedWhereTheSpecificationTakesASingleNumber(): void
    {
        self::assertSame(
            'Enter a single number.',
            (new UnitType())->validate(['min' => 100, 'max' => 240, 'unit' => 'V'], $this->voltage(range: false))
        );
    }

    /**
     * @return array<string, array{mixed, string}>
     */
    public static function formatted(): array
    {
        return [
            'a single number' => [['value' => 230, 'unit' => 'V'], '230 V'],
            'a range' => [['min' => 100, 'max' => 240, 'unit' => 'V'], '100–240 V'],
            'no trailing zeros' => [['value' => 1.5, 'unit' => 'kV'], '1.5 kV'],
            'a unit not among the options' => [['value' => 2, 'unit' => 'mV'], '2 mV'],
            'text' => ['180 V', ''],
        ];
    }

    /**
     * @dataProvider formatted
     */
    public function testThePlainValueIsTheNumberAndTheUnitsSymbol(mixed $value, string $expected): void
    {
        self::assertSame($expected, (new UnitType())->plainValue($value, $this->voltage(range: true)));
    }

    public function testTheStoresLocaleFormatsTheNumber(): void
    {
        $locale = new WP_Locale();
        $locale->number_format = ['decimal_point' => ',', 'thousands_sep' => '.'];
        // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- The store's locale is a global.
        $GLOBALS['wp_locale'] = $locale;

        self::assertSame(
            '1.250,5 V',
            (new UnitType())->plainValue(['value' => 1250.5, 'unit' => 'V'], $this->voltage(range: true))
        );
    }

    public function testASpaceGroupingThousandsIsTheCharacterNotTheEntity(): void
    {
        $locale = new WP_Locale();
        $locale->number_format = ['decimal_point' => ',', 'thousands_sep' => '&nbsp;'];
        // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- The store's locale is a global.
        $GLOBALS['wp_locale'] = $locale;

        self::assertSame(
            "1\u{00A0}250,5 V",
            (new UnitType())->plainValue(['value' => 1250.5, 'unit' => 'V'], $this->voltage(range: true))
        );
    }

    public function testReversedBoundsAreReadInOrder(): void
    {
        $options = SpecificationOptions::fromMap([
            'units' => [['id' => 'V', 'symbol' => 'V']],
            'min' => 400,
            'max' => 100,
        ]);

        self::assertNull((new UnitType())->validate(['value' => 230, 'unit' => 'V'], $options));
    }

    private function voltage(bool $range): SpecificationOptions
    {
        return SpecificationOptions::fromMap([
            'kind' => 'voltage',
            'units' => [['id' => 'V', 'symbol' => 'V'], ['id' => 'kV', 'symbol' => 'kV']],
            'default_unit' => 'V',
            'range' => $range,
            'decimals' => 2,
            'min' => 100,
            'max' => 400,
        ]);
    }
}
