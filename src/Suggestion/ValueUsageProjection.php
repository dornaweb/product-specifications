<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Suggestion;

use Amiut\ProductSpecs\ProductValues\ProductValueRepository;
use Amiut\ProductSpecs\ProductValues\ProductValueWriter;
use Amiut\ProductSpecs\Specification\SpecificationDefinition;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\SpecificationTaxonomy;
use Amiut\ProductSpecs\Specification\Type\TextOptions;
use Amiut\ProductSpecs\Specification\Type\TextType;
use WP_Post;

/**
 * Keeps each suggesting specification's usage index in step with the value maps. An
 * index that is missing is built from the maps, so it never has to be created ahead.
 */
final class ValueUsageProjection
{
    private const DEFINITION_META_KEYS = ['attr_type', 'attr_values'];
    private const PRODUCT_TYPES = ['product', 'product_variation'];

    public function __construct(
        private readonly ValueUsageRepository $usage,
        private readonly SpecificationDefinitionRepository $definitions,
        private readonly ProductValueRepository $values
    ) {
    }

    public function register(): void
    {
        add_action(ProductValueWriter::SAVED_ACTION, [$this, 'applySave'], 10, 3);
        add_action(ProductValueWriter::REBUILD_ACTION, [$this, 'rebuildAll']);
        add_action('trashed_post', [$this, 'forgetProduct']);
        add_action('untrashed_post', [$this, 'restoreProduct']);
        add_action('before_delete_post', [$this, 'beforeDeleteProduct'], 10, 2);
        add_action('added_term_meta', [$this, 'definitionChanged'], 10, 3);
        add_action('updated_term_meta', [$this, 'definitionChanged'], 10, 3);
        add_action('delete_term', [$this, 'specificationDeleted'], 10, 3);
    }

    /**
     * @return list<array{value: string, count: int, learned: bool}>
     */
    public function suggestionsFor(SpecificationDefinition $definition): array
    {
        $termId = $definition->id();

        if ($termId === null || !self::suggests($definition)) {
            return [];
        }

        $usage = $this->usage->find($termId) ?? $this->build($termId, $definition);

        return $usage->suggestions(TextOptions::of($definition->options()));
    }

    /**
     * @param array<string, mixed> $newValues
     * @param array<string, mixed> $oldValues
     */
    public function applySave(int $postId, array $newValues, array $oldValues): void
    {
        $this->applyChange($oldValues, $newValues);
    }

    public function forgetProduct(int $postId): void
    {
        if ($this->isProduct($postId)) {
            $this->applyChange($this->values->read($postId)->values(), []);
        }
    }

    public function restoreProduct(int $postId): void
    {
        if ($this->isProduct($postId)) {
            $this->applyChange([], $this->values->read($postId)->values());
        }
    }

    public function beforeDeleteProduct(int $postId, mixed $post = null): void
    {
        if (!$post instanceof WP_Post || $post->post_status !== 'trash') {
            $this->forgetProduct($postId);
        }
    }

    public function definitionChanged(int $metaId, int $termId, string $metaKey): void
    {
        if (!in_array($metaKey, self::DEFINITION_META_KEYS, true)) {
            return;
        }

        $definition = $this->definitions->find($termId);

        if ($definition === null) {
            return;
        }

        if (!self::suggests($definition)) {
            $this->usage->delete($termId);

            return;
        }

        if ($this->usage->find($termId) === null) {
            $this->build($termId, $definition);
        }
    }

    public function specificationDeleted(int $termId, int $termTaxonomyId, string $taxonomy): void
    {
        if ($taxonomy === SpecificationTaxonomy::KEY) {
            $this->usage->delete($termId);
        }
    }

    public function rebuildAll(): void
    {
        foreach ($this->definitions->findAll() as $definition) {
            $termId = $definition->id();

            if ($termId !== null && self::suggests($definition)) {
                $this->build($termId, $definition);
            }
        }
    }

    /**
     * @param array<string, mixed> $oldValues
     * @param array<string, mixed> $newValues
     */
    private function applyChange(array $oldValues, array $newValues): void
    {
        foreach (array_keys($oldValues + $newValues) as $key) {
            $old = $oldValues[$key] ?? null;
            $new = $newValues[$key] ?? null;

            if ($old === $new) {
                continue;
            }

            $definition = $this->definitions->findByKey((string) $key);
            $termId = $definition?->id();

            if ($definition === null || $termId === null || !self::suggests($definition)) {
                continue;
            }

            $usage = $this->usage->find($termId);

            if ($usage === null) {
                $this->build($termId, $definition);
                continue;
            }

            $this->usage->save($termId, $usage->withChange($old, $new));
        }
    }

    private function build(int $termId, SpecificationDefinition $definition): ValueUsage
    {
        $usage = ValueUsage::empty();
        $key = $definition->key();

        foreach ($this->values->valueMapsWith($key) as $map) {
            $usage = $usage->withChange(null, $map[$key]);
        }

        $this->usage->save($termId, $usage);

        return $usage;
    }

    private function isProduct(int $postId): bool
    {
        return in_array(get_post_type($postId), self::PRODUCT_TYPES, true);
    }

    private static function suggests(SpecificationDefinition $definition): bool
    {
        return $definition->typeId() === TextType::ID
            && TextOptions::of($definition->options())->suggests();
    }
}
