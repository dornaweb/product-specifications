<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use WC_Product;

final class TrueFalseType implements SpecificationType
{
    public const ID = 'true_false';

    public function id(): string
    {
        return self::ID;
    }

    public function label(): string
    {
        return __('Yes or no', 'product-specifications');
    }

    /**
     * @return array<string, mixed>
     */
    public function optionsSchema(): array
    {
        return YesNoDisplay::optionsSchema();
    }

    public function sanitize(mixed $input, SpecificationOptions $options): mixed
    {
        return $this->asBoolean($input);
    }

    public function validate(mixed $input, SpecificationOptions $options): ?string
    {
        return null;
    }

    public function value(WC_Product $product, mixed $stored, SpecificationOptions $options): mixed
    {
        return $this->asBoolean($stored);
    }

    public function plainValue(mixed $value, SpecificationOptions $options): string
    {
        $boolean = $this->asBoolean($value);

        return $boolean === null ? '' : YesNoDisplay::fromOptions($options)->label($boolean);
    }

    public function render(mixed $value, SpecificationOptions $options): string
    {
        $boolean = $this->asBoolean($value);

        return $boolean === null ? '' : YesNoDisplay::fromOptions($options)->render($boolean);
    }

    private function asBoolean(mixed $input): ?bool
    {
        if (is_bool($input)) {
            return $input;
        }

        if ($input === 'yes' || $input === '1' || $input === 1) {
            return true;
        }

        if ($input === 'no' || $input === '0' || $input === 0) {
            return false;
        }

        return null;
    }
}
