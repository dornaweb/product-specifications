<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type;

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
        return OptionsSchema::none();
    }

    public function sanitize(mixed $input): mixed
    {
        return $this->asBoolean($input);
    }

    public function value(WC_Product $product, mixed $stored): mixed
    {
        return $this->asBoolean($stored);
    }

    public function render(mixed $value): string
    {
        $boolean = $this->asBoolean($value);

        if ($boolean === null) {
            return '';
        }

        return $boolean
            ? esc_html__('Yes', 'product-specifications')
            : esc_html__('No', 'product-specifications');
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
