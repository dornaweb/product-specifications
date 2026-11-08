<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification;

use Amiut\ProductSpecs\Slug\SlugLookup;
use WP_Error;
use WP_Term;

final class SpecificationDefinitionRepository
{
    public function __construct(private readonly SpecificationDefinitionMapper $mapper)
    {
    }

    public function find(int $id): ?SpecificationDefinition
    {
        $term = get_term($id, SpecificationTaxonomy::KEY);

        if (!$term instanceof WP_Term) {
            return null;
        }

        return $this->mapper->fromTerm($term);
    }

    public function findByKey(string $key): ?SpecificationDefinition
    {
        $term = SlugLookup::term($key, SpecificationTaxonomy::KEY);

        return $term === null ? null : $this->mapper->fromTerm($term);
    }

    /**
     * @param array<string, mixed> $args
     * @return list<SpecificationDefinition>
     */
    public function findAll(array $args = []): array
    {
        $terms = get_terms($this->queryArguments($args));

        if ($terms instanceof WP_Error) {
            return [];
        }

        if (!is_array($terms)) {
            return [];
        }

        $specifications = [];

        foreach ($terms as $term) {
            if ($term instanceof WP_Term) {
                $specifications[] = $this->mapper->fromTerm($term);
            }
        }

        return $specifications;
    }

    public function save(SpecificationDefinition $specification): int
    {
        $id = $specification->id();
        $result = is_int($id)
            ? wp_update_term($id, SpecificationTaxonomy::KEY, $this->mapper->updateArguments($specification))
            : wp_insert_term($specification->name(), SpecificationTaxonomy::KEY, $this->mapper->insertArguments($specification));

        if ($result instanceof WP_Error) {
            return 0;
        }

        $termId = is_int($result['term_id']) ? $result['term_id'] : 0;

        if ($termId < 1) {
            return 0;
        }

        foreach ($this->mapper->termMeta($specification) as $metaKey => $metaValue) {
            update_term_meta($termId, $metaKey, $metaValue);
        }

        return $termId;
    }

    public function delete(int $id): bool
    {
        $result = wp_delete_term($id, SpecificationTaxonomy::KEY);

        return is_bool($result) ? $result : !$result instanceof WP_Error;
    }

    /**
     * @param array<string, mixed> $args
     * @return array<string, mixed>
     * @phpstan-return array{taxonomy: string, hide_empty: bool, search?: string, number?: int, offset?: int, orderby?: string, order?: string}
     */
    private function queryArguments(array $args): array
    {
        $queryArgs = [
            'taxonomy' => SpecificationTaxonomy::KEY,
            'hide_empty' => false,
        ];

        foreach (['search', 'orderby', 'order'] as $key) {
            if (isset($args[$key]) && is_string($args[$key])) {
                $queryArgs[$key] = $args[$key];
            }
        }

        foreach (['number', 'offset'] as $key) {
            if (isset($args[$key]) && is_int($args[$key])) {
                $queryArgs[$key] = $args[$key];
            }
        }

        return $queryArgs;
    }
}
