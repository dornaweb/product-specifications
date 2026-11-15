<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\Number;

final class NumberFormat
{
    public function __construct(private readonly int $decimals, private readonly bool $thousands)
    {
    }

    /**
     * Formatted for the store's locale, without trailing zeros.
     */
    public function format(float $number): string
    {
        $formatted = $this->localized($number, $this->decimals);

        if ($this->decimals === 0) {
            return $formatted;
        }

        $point = $this->decimalPoint();
        $trimmed = rtrim($formatted, '0');

        return str_ends_with($trimmed, $point) ? substr($trimmed, 0, -strlen($point)) : $trimmed;
    }

    /**
     * WordPress writes a space used as the thousands separator as `&nbsp;`. Plain text needs
     * the character itself.
     */
    private function localized(float $number, int $decimals): string
    {
        if (!$this->thousands) {
            return number_format($number, $decimals, $this->decimalPoint(), '');
        }

        return str_replace('&nbsp;', "\u{00A0}", number_format_i18n($number, $decimals));
    }

    private function decimalPoint(): string
    {
        return substr(str_replace('&nbsp;', "\u{00A0}", number_format_i18n(0.5, 1)), 1, -1);
    }
}
