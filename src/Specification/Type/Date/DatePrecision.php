<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\Date;

use Amiut\ProductSpecs\Specification\SpecificationOptions;

enum DatePrecision: string
{
    case Year = 'year';
    case Month = 'month';
    case Day = 'day';

    private const OPTION = 'precision';
    private const FALLBACK_DAY_FORMAT = 'Y-m-d';

    public static function fromOptions(SpecificationOptions $options): self
    {
        $stored = $options->toArray()[self::OPTION] ?? null;

        return is_string($stored) ? self::tryFrom($stored) ?? self::Day : self::Day;
    }

    /**
     * @return array<string, mixed>
     */
    public static function optionsSchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                self::OPTION => [
                    'type' => 'string',
                    'enum' => array_map(static fn (self $precision): string => $precision->value, self::cases()),
                ],
            ],
        ];
    }

    public function parts(): int
    {
        return match ($this) {
            self::Year => 1,
            self::Month => 2,
            self::Day => 3,
        };
    }

    public function format(): string
    {
        $dayFormat = get_option('date_format');

        return match ($this) {
            self::Year => 'Y',
            self::Month => _x('F Y', 'date format for a month and year', 'product-specifications'),
            self::Day => is_string($dayFormat) && $dayFormat !== '' ? $dayFormat : self::FALLBACK_DAY_FORMAT,
        };
    }

    public function rejection(): string
    {
        return match ($this) {
            self::Year => __('Enter a year with four digits.', 'product-specifications'),
            self::Month => __('Choose a month and enter a year.', 'product-specifications'),
            self::Day => __('Enter a date.', 'product-specifications'),
        };
    }
}
