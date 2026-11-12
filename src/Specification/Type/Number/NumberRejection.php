<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\Number;

enum NumberRejection
{
    case NotANumber;
    case UnitNotAllowed;
    case RangeNotAllowed;
    case ReversedRange;

    public function message(): string
    {
        return match ($this) {
            self::NotANumber => __('Enter a number.', 'product-specifications'),
            self::UnitNotAllowed => __('Choose one of the units.', 'product-specifications'),
            self::RangeNotAllowed => __('Enter a single number.', 'product-specifications'),
            self::ReversedRange => __('The first number cannot be larger than the second.', 'product-specifications'),
        };
    }
}
