<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Suggestion;

use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\SpecificationTaxonomy;

final class SuggestionsRestField
{
    public const FIELD = 'suggestions';

    public function __construct(
        private readonly SpecificationDefinitionRepository $definitions,
        private readonly ValueUsageProjection $projection
    ) {
    }

    public function register(): void
    {
        register_rest_field(
            SpecificationTaxonomy::KEY,
            self::FIELD,
            [
                'get_callback' => [$this, 'read'],
                'schema' => $this->schema(),
            ]
        );
    }

    /**
     * @param array<string, mixed> $term
     * @return list<array{value: string, count: int, learned: bool}>
     */
    public function read(array $term): array
    {
        $termId = is_numeric($term['id'] ?? null) ? (int) $term['id'] : 0;
        $definition = $this->definitions->find($termId);

        return $definition === null ? [] : $this->projection->suggestionsFor($definition);
    }

    /**
     * @return array<string, mixed>
     */
    private function schema(): array
    {
        return [
            'description' => __(
                'Values offered while typing, the most used first.',
                'product-specifications'
            ),
            'type' => 'array',
            'context' => ['view', 'edit'],
            'readonly' => true,
            'items' => [
                'type' => 'object',
                'properties' => [
                    'value' => ['type' => 'string'],
                    'count' => ['type' => 'integer'],
                    'learned' => ['type' => 'boolean'],
                ],
            ],
        ];
    }
}
