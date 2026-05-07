<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet;

/**
 * @phpstan-type SchemaType \ReturnType<self, 'schema'>
 */
final class StructureMetaDefinition
{
    public const KEY = 'specification_set_structure';
    public const CURRENT_VERSION = 1;

    /**
     * @return array<string, mixed>
     * @phpstan-return array @phpstan-infer-return
     */
    public static function default(): array
    {
        return [
            'schema_version' => self::CURRENT_VERSION,
            'groups' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     * @phpstan-return array @phpstan-infer-return
     */
    public function schema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => [
                'schema_version',
                'groups',
            ],
            'properties' => [
                'schema_version' => [
                    'type' => 'integer',
                    'minimum' => self::CURRENT_VERSION,
                    'maximum' => self::CURRENT_VERSION,
                ],
                'groups' => [
                    'type' => 'array',
                    'items' => $this->groupSchema(),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     * @phpstan-return array @phpstan-infer-return
     */
    private function groupSchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => [
                'id',
                'key',
                'label',
                'visible',
                'items',
            ],
            'properties' => [
                'id' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'pattern' => '^[a-z0-9_-]+$',
                ],
                'key' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'pattern' => '^[a-z0-9_-]+$',
                ],
                'label' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'description' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'visible' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
                'items' => [
                    'type' => 'array',
                    'items' => $this->itemSchema(),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     * @phpstan-return array @phpstan-infer-return
     */
    private function itemSchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => [
                'type',
                'term_id',
                'key',
            ],
            'properties' => [
                'type' => [
                    'type' => 'string',
                    'enum' => [
                        'specification',
                    ],
                ],
                'term_id' => [
                    'type' => 'integer',
                    'minimum' => 1,
                ],
                'key' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'pattern' => '^[a-z0-9_-]+$',
                ],
            ],
        ];
    }
}
