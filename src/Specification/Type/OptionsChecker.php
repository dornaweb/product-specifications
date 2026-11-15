<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use WP_Error;

/**
 * The type owns what a valid configuration is, so its schema both rejects a bad
 * payload and decides what a good one becomes. Storing the raw input instead would
 * let the two disagree: the schema coerces a bare string into a one-item list, and
 * normalization alone would discard it.
 */
final class OptionsChecker
{
    public function __construct(private readonly SpecificationTypeRegistry $types)
    {
    }

    /**
     * @return array<string, mixed>|WP_Error
     */
    public function check(string $typeId, mixed $options, string $param): array|WP_Error
    {
        $schema = $this->types->get($typeId)->optionsSchema();
        $invalid = rest_validate_value_from_schema($options, $schema, $param);

        if ($invalid instanceof WP_Error) {
            return $invalid;
        }

        $sanitized = rest_sanitize_value_from_schema($options, $schema, $param);

        if ($sanitized instanceof WP_Error) {
            return $sanitized;
        }

        return SpecificationOptions::fromStorage($sanitized)->toArray();
    }
}
