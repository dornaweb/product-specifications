<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification;

use Amiut\ProductSpecs\Specification\Type\TextType;
use WP_Term;

final class SpecificationDefinitionMapper
{
    public function fromTerm(WP_Term $term): SpecificationDefinition
    {
        return SpecificationDefinition::fromStorage(
            $term->term_id,
            $term->name,
            $term->slug,
            $term->description,
            $this->typeId($term->term_id),
            SpecificationOptions::fromStorage(get_term_meta($term->term_id, 'attr_values', true)),
            get_term_meta($term->term_id, 'attr_default', true)
        );
    }

    /**
     * @return array{name: string, slug: string, description: string}
     */
    public function insertArguments(SpecificationDefinition $specification): array
    {
        return [
            'name' => $specification->name(),
            'slug' => $specification->key(),
            'description' => $specification->description(),
        ];
    }

    /**
     * The slug is absent by construction. It is the identity the value map, the
     * set structures and every export are keyed by, and a rename must not reach
     * it.
     *
     * @return array{name: string, description: string}
     */
    public function updateArguments(SpecificationDefinition $specification): array
    {
        return [
            'name' => $specification->name(),
            'description' => $specification->description(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function termMeta(SpecificationDefinition $specification): array
    {
        return [
            'attr_type' => $specification->typeId(),
            'attr_values' => $specification->options()->toArray(),
            'attr_default' => $specification->defaultValue(),
        ];
    }

    /**
     * An unrecognized id is kept as stored. Resolving it against the registry
     * here would overwrite it on the next save.
     */
    private function typeId(int $termId): string
    {
        $stored = get_term_meta($termId, 'attr_type', true);
        $typeId = is_scalar($stored) ? (string) $stored : '';

        return $typeId === '' ? TextType::ID : $typeId;
    }
}
