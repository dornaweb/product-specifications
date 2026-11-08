<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\Unit;

final class DecimalNumber
{
    /**
     * A number as typed: digits with a comma or a dot before the decimals. Thousands
     * separators are not read, because "1.000" is a thousand in some locales and one in others.
     */
    public static function parse(mixed $raw): ?float
    {
        if (is_int($raw) || is_float($raw)) {
            return is_finite((float) $raw) ? (float) $raw : null;
        }

        if (!is_string($raw)) {
            return null;
        }

        $normalized = rtrim(str_replace(',', '.', trim($raw)), '.');

        return preg_match('/^-?(\d+(\.\d+)?|\.\d+)$/', $normalized) === 1 ? (float) $normalized : null;
    }

    /**
     * Formatted for the store's locale, without trailing zeros.
     */
    public static function format(float $number, int $decimals): string
    {
        $formatted = self::localized($number, $decimals);

        if ($decimals === 0) {
            return $formatted;
        }

        $point = substr(self::localized(0.5, 1), 1, -1);
        $trimmed = rtrim($formatted, '0');

        return str_ends_with($trimmed, $point) ? substr($trimmed, 0, -strlen($point)) : $trimmed;
    }

    /**
     * WordPress writes a space used as the thousands separator as `&nbsp;`. Plain text needs
     * the character itself.
     */
    private static function localized(float $number, int $decimals): string
    {
        return str_replace('&nbsp;', "\u{00A0}", number_format_i18n($number, $decimals));
    }
}
