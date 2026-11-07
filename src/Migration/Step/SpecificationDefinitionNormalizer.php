<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration\Step;

use Amiut\ProductSpecs\Specification\SpecificationSlug;
use Amiut\ProductSpecs\Specification\Type\SelectType;

/**
 * Choices and defaults are stored HTML-encoded, and not always with the same encoding,
 * so both are decoded before they are compared or kept.
 */
final class SpecificationDefinitionNormalizer
{
    private const MERGED_INTO_SELECT = 'radio';

    public function typeId(string $stored): string
    {
        return $stored === self::MERGED_INTO_SELECT ? SelectType::ID : $stored;
    }

    /**
     * @param array<array-key, mixed> $stored
     * @return array<array-key, mixed>
     */
    public function choices(array $stored): array
    {
        return array_map(
            static fn (mixed $choice): mixed => is_string($choice) ? self::decoded($choice) : $choice,
            $stored
        );
    }

    /**
     * @param list<string> $choices Already decoded.
     */
    public function defaultValue(string $typeId, mixed $stored, array $choices): mixed
    {
        $decoded = is_string($stored) ? self::decoded($stored) : $stored;

        if ($typeId !== SelectType::ID) {
            return $decoded;
        }

        return is_string($decoded) && in_array($decoded, $choices, true) ? $decoded : null;
    }

    public function slug(string $stored): string
    {
        return SpecificationSlug::preserved($stored)->value();
    }

    private static function decoded(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
