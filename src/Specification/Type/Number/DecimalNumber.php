<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\Number;

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
}
