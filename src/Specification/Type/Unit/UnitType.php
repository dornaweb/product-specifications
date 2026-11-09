<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\Unit;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use Amiut\ProductSpecs\Specification\Type\SpecificationType;
use WC_Product;

final class UnitType implements SpecificationType
{
    public const ID = 'unit';

    public function id(): string
    {
        return self::ID;
    }

    public function label(): string
    {
        return __('Number with unit', 'product-specifications');
    }

    /**
     * @return array<string, mixed>
     */
    public function optionsSchema(): array
    {
        return UnitOptions::schema();
    }

    public function sanitize(mixed $input, SpecificationOptions $options): mixed
    {
        return UnitOptions::fromOptions($options)->read($input)?->toStorage();
    }

    public function validate(mixed $input, SpecificationOptions $options): ?string
    {
        if (Measurement::isEmpty($input)) {
            return null;
        }

        $units = UnitOptions::fromOptions($options);
        $measurement = $units->read($input);

        return $measurement === null ? MeasurementRejection::NotANumber->message() : $units->rejection($measurement);
    }

    public function value(WC_Product $product, mixed $stored, SpecificationOptions $options): mixed
    {
        return Measurement::fromData($stored, '')?->toStorage();
    }

    public function plainValue(mixed $value, SpecificationOptions $options): string
    {
        $units = UnitOptions::fromOptions($options);
        $measurement = $units->read($value);

        return $measurement === null ? '' : $units->format($measurement);
    }

    public function render(mixed $value, SpecificationOptions $options): string
    {
        return esc_html(str_replace(' ', "\u{00A0}", $this->plainValue($value, $options)));
    }
}
