<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type;

/**
 * Two text values are the same value when they differ only in letter case or in
 * whitespace. The editor applies the same rule, so both sides agree on duplicates.
 */
final class ValueKey
{
    public static function of(string $value): string
    {
        $collapsed = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return mb_strtolower(trim($collapsed, ' '));
    }
}
