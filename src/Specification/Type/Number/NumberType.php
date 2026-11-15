<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\Number;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use Amiut\ProductSpecs\Specification\Type\SpecificationType;
use WC_Product;

final class NumberType implements SpecificationType
{
    /**
     * Stored as the type of every specification that uses it, so it does not follow the
     * class name.
     */
    public const ID = 'unit';

    public function id(): string
    {
        return self::ID;
    }

    public function label(): string
    {
        return __('Number', 'product-specifications');
    }

    /**
     * @return array<string, mixed>
     */
    public function optionsSchema(): array
    {
        return NumberOptions::schema();
    }

    public function sanitize(mixed $input, SpecificationOptions $options): mixed
    {
        return NumberOptions::fromOptions($options)->read($input)?->toStorage();
    }

    public function validate(mixed $input, SpecificationOptions $options): ?string
    {
        if (NumberValue::isEmpty($input)) {
            return null;
        }

        $numberOptions = NumberOptions::fromOptions($options);
        $number = $numberOptions->read($input);

        return $number === null ? NumberRejection::NotANumber->message() : $numberOptions->rejection($number);
    }

    public function value(WC_Product $product, mixed $stored, SpecificationOptions $options): mixed
    {
        return NumberValue::fromData($stored, '')?->toStorage();
    }

    public function plainValue(mixed $value, SpecificationOptions $options): string
    {
        $numberOptions = NumberOptions::fromOptions($options);
        $number = $numberOptions->read($value);

        return $number === null ? '' : $numberOptions->format($number);
    }

    public function render(mixed $value, SpecificationOptions $options): string
    {
        return esc_html(str_replace(' ', "\u{00A0}", $this->plainValue($value, $options)));
    }
}
