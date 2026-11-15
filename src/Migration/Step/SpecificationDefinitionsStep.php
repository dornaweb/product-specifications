<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration\Step;

use Amiut\ProductSpecs\Migration\ChunkResult;
use Amiut\ProductSpecs\Migration\IdBatch;
use Amiut\ProductSpecs\Migration\MigrationStep;
use Amiut\ProductSpecs\Specification\SpecificationOptions;
use Amiut\ProductSpecs\Specification\SpecificationTaxonomy;
use WP_Error;
use WP_Term;

final class SpecificationDefinitionsStep implements MigrationStep
{
    public const KEY = 'specifications';

    public function __construct(private readonly SpecificationDefinitionNormalizer $normalizer)
    {
    }

    public function key(): string
    {
        return self::KEY;
    }

    public function total(): int
    {
        return count(array_filter($this->termIds(), [$this, 'needsMigration']));
    }

    public function run(int $cursor, int $limit): ChunkResult
    {
        $batch = IdBatch::after($this->termIds(), $cursor, $limit);
        $processed = 0;
        $errors = [];

        foreach ($batch->ids() as $termId) {
            if (!$this->needsMigration($termId)) {
                continue;
            }

            $processed++;

            if (!$this->migrate($termId)) {
                $errors[$termId] = __('The specification could not be updated.', 'product-specifications');
            }
        }

        return $batch->result($processed, [], $errors);
    }

    private function needsMigration(int $termId): bool
    {
        $stored = $this->stored($termId);

        return $stored !== null && $stored !== $this->normalized($stored);
    }

    private function migrate(int $termId): bool
    {
        $stored = $this->stored($termId);

        if ($stored === null) {
            return false;
        }

        $normalized = $this->normalized($stored);

        $metaKeys = ['type' => 'attr_type', 'options' => 'attr_values', 'default' => 'attr_default'];

        foreach ($metaKeys as $field => $metaKey) {
            if ($normalized[$field] !== $stored[$field]) {
                update_term_meta($termId, $metaKey, $normalized[$field]);
            }
        }

        if ($normalized['slug'] === $stored['slug']) {
            return true;
        }

        $updated = wp_update_term($termId, SpecificationTaxonomy::KEY, ['slug' => $normalized['slug']]);

        return !$updated instanceof WP_Error;
    }

    /**
     * @return array{type: string, options: mixed, default: mixed, slug: string}|null
     */
    private function stored(int $termId): ?array
    {
        $term = get_term($termId, SpecificationTaxonomy::KEY);

        if (!$term instanceof WP_Term) {
            return null;
        }

        $type = get_term_meta($termId, 'attr_type', true);

        return [
            'type' => is_scalar($type) ? (string) $type : '',
            'options' => get_term_meta($termId, 'attr_values', true),
            'default' => get_term_meta($termId, 'attr_default', true),
            'slug' => $term->slug,
        ];
    }

    /**
     * @param array{type: string, options: mixed, default: mixed, slug: string} $stored
     * @return array{type: string, options: mixed, default: mixed, slug: string}
     */
    private function normalized(array $stored): array
    {
        $type = $this->normalizer->typeId($stored['type']);
        $options = $this->normalizer->options($stored['options']);
        $choices = SpecificationOptions::fromStorage($options)->choices();

        return [
            'type' => $type,
            'options' => $options,
            'default' => $this->normalizer->defaultValue($type, $stored['default'], $choices) ?? '',
            'slug' => $this->normalizer->slug($stored['slug']),
        ];
    }

    /**
     * @return list<int>
     */
    private function termIds(): array
    {
        $terms = get_terms([
            'taxonomy' => SpecificationTaxonomy::KEY,
            'hide_empty' => false,
        ]);

        $ids = [];

        foreach (is_array($terms) ? $terms : [] as $term) {
            if ($term instanceof WP_Term) {
                $ids[] = $term->term_id;
            }
        }

        return $ids;
    }
}
