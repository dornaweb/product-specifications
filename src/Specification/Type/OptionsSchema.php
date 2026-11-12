<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type;

use Amiut\ProductSpecs\Specification\SpecificationOptions;

final class OptionsSchema
{
    /**
     * @return array<string, mixed>
     */
    public static function none(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function choices(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => [SpecificationOptions::CHOICES_KEY],
            'properties' => [
                SpecificationOptions::CHOICES_KEY => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'minItems' => 1,
                ],
            ],
        ];
    }
}
