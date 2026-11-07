<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet;

final class StructureMetaDefinition
{
    public const KEY = 'specification_set_structure';
    public const CURRENT_VERSION = 1;

    /**
     * @return array<string, mixed>
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
     */
    private function groupSchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => [
                'key',
                'label',
                'items',
            ],
            'properties' => [
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
     */
    private function itemSchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => [
                'term_id',
                'slug',
            ],
            'properties' => [
                'term_id' => [
                    'type' => 'integer',
                    'minimum' => 1,
                ],
                'slug' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'pattern' => '^[a-z0-9_%-]+$',
                ],
                'visible' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
                'comparable' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
            ],
        ];
    }
}
