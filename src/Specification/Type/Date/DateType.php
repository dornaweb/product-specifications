<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\Date;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use Amiut\ProductSpecs\Specification\Type\SpecificationType;
use WC_Product;

final class DateType implements SpecificationType
{
    public const ID = 'date';

    public function id(): string
    {
        return self::ID;
    }

    public function label(): string
    {
        return __('Date', 'product-specifications');
    }

    /**
     * @return array<string, mixed>
     */
    public function optionsSchema(): array
    {
        return DatePrecision::optionsSchema();
    }

    public function sanitize(mixed $input, SpecificationOptions $options): mixed
    {
        return $this->read($input, $options)?->iso();
    }

    public function validate(mixed $input, SpecificationOptions $options): ?string
    {
        if ($input === null || (is_string($input) && trim($input) === '')) {
            return null;
        }

        $precision = DatePrecision::fromOptions($options);
        $date = CalendarDate::fromIso($input);

        return $date !== null && $date->covers($precision) ? null : $precision->rejection();
    }

    public function value(WC_Product $product, mixed $stored, SpecificationOptions $options): mixed
    {
        return $this->read($stored, $options)?->iso();
    }

    public function plainValue(mixed $value, SpecificationOptions $options): string
    {
        return $this->read($value, $options)?->formatted() ?? '';
    }

    public function render(mixed $value, SpecificationOptions $options): string
    {
        return esc_html($this->plainValue($value, $options));
    }

    private function read(mixed $input, SpecificationOptions $options): ?CalendarDate
    {
        return CalendarDate::fromIso($input)?->cutTo(DatePrecision::fromOptions($options));
    }
}
