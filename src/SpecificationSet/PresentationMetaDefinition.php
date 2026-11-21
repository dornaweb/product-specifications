<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet;

final class PresentationMetaDefinition
{
    public const KEY = 'specification_set_presentation';
    public const CURRENT_VERSION = 1;

    /**
     * @return array{schema_version: int, empty_rows: string, empty_groups: string, comparison: bool}
     */
    public static function default(): array
    {
        return SpecificationSetPresentation::fromStored(null)->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'schema_version' => [
                    'type' => 'integer',
                ],
                'empty_rows' => [
                    'type' => 'string',
                    'enum' => ['hide', 'show'],
                ],
                'empty_groups' => [
                    'type' => 'string',
                    'enum' => ['hide', 'show'],
                ],
                'comparison' => [
                    'type' => 'boolean',
                ],
            ],
        ];
    }
}
