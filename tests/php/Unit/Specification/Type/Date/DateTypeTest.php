<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Type\Date;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use Amiut\ProductSpecs\Specification\Type\Date\DateType;
use PHPUnit\Framework\TestCase;

final class DateTypeTest extends TestCase
{
    /**
     * @return array<string, array{mixed, string, string|null}>
     */
    public static function storedValues(): array
    {
        return [
            'a full date' => ['2026-03-14', 'day', '2026-03-14'],
            'a full date cut to the month' => ['2026-03-14', 'month', '2026-03'],
            'a full date cut to the year' => ['2026-03-14', 'year', '2026'],
            'a month cut to the year' => ['2026-03', 'year', '2026'],
            'a month kept where a day is expected' => ['2026-03', 'day', '2026-03'],
            'a year kept where a day is expected' => ['2026', 'day', '2026'],
            'surrounding spaces' => [' 2026-03 ', 'month', '2026-03'],
            'a year as a number' => [2026, 'year', '2026'],
            'no precision means a day' => ['2026-03-14', '', '2026-03-14'],
            'a day that does not exist' => ['2026-02-30', 'day', null],
            'a month that does not exist' => ['2026-13', 'month', null],
            'a year of two digits' => ['26', 'year', null],
            'a localized date' => ['14.03.2026', 'day', null],
            'a date with a time' => ['2026-03-14T10:00:00', 'day', null],
            'empty' => ['', 'day', null],
        ];
    }

    /**
     * @dataProvider storedValues
     */
    public function testAValueIsStoredAsIsoCutToThePrecision(mixed $input, string $precision, ?string $stored): void
    {
        self::assertSame($stored, (new DateType())->sanitize($input, $this->options($precision)));
    }

    /**
     * @return array<string, array{mixed, string, string|null}>
     */
    public static function inputs(): array
    {
        return [
            'empty' => ['', 'day', null],
            'nothing' => [null, 'month', null],
            'a full date' => ['2026-03-14', 'day', null],
            'more precise than needed' => ['2026-03-14', 'year', null],
            'a month where a day is needed' => ['2026-03', 'day', 'Enter a date.'],
            'a year where a month is needed' => ['2026', 'month', 'Choose a month and enter a year.'],
            'a month without its year' => ['-03', 'month', 'Choose a month and enter a year.'],
            'a year without its month' => ['2026-', 'month', 'Choose a month and enter a year.'],
            'a day that does not exist' => ['2026-02-30', 'day', 'Enter a date.'],
            'a year of two digits' => ['26', 'year', 'Enter a year with four digits.'],
            'a word' => ['March', 'month', 'Choose a month and enter a year.'],
        ];
    }

    /**
     * @dataProvider inputs
     */
    public function testAValueIsRejectedWhenItIsNotADateOfThePrecision(
        mixed $input,
        string $precision,
        ?string $rejection
    ): void {
        self::assertSame($rejection, (new DateType())->validate($input, $this->options($precision)));
    }

    private function options(string $precision): SpecificationOptions
    {
        return SpecificationOptions::fromMap($precision === '' ? [] : ['precision' => $precision]);
    }
}
