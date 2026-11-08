<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ProductValues;

use Amiut\ProductSpecs\Specification\SpecificationDefinition;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetRepository;
use RuntimeException;

final class ProductValueWriter
{
    public const SAVED_ACTION = 'product_specifications_values_saved';
    public const REBUILD_ACTION = 'product_specifications_rebuild';

    public function __construct(
        private readonly ProductValueRepository $repository,
        private readonly SpecificationDefinitionRepository $definitions,
        private readonly SpecificationTypeRegistry $types,
        private readonly SpecificationSetRepository $sets
    ) {
    }

    /**
     * The submitted map becomes the whole value map, so an absent key is removed. Without
     * a set the product is unassigned and keeps no values.
     *
     * @param array<string, mixed> $submittedValues
     * @return array<string, mixed>
     */
    public function replace(int $postId, ?string $setSlug, array $submittedValues): array
    {
        $isVariation = $this->productType($postId) === 'product_variation';
        $setSlug = $setSlug === null ? null : $this->storedSetSlug($setSlug);
        $oldValues = $this->repository->read($postId)->values();
        $newValues = $setSlug === null ? [] : $this->sanitizedReplacement($submittedValues, $isVariation);

        $this->write($postId, $setSlug, $newValues);
        do_action(self::SAVED_ACTION, $postId, $newValues, $oldValues);

        return $newValues;
    }

    /**
     * Each value is laid over the stored ones. A value with no registered specification,
     * or one its type does not accept, is left out. Without a set the product keeps its own.
     *
     * @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    public function merge(int $postId, ?string $setSlug, array $values): array
    {
        $this->productType($postId);
        $setSlug = $setSlug === null ? null : $this->storedSetSlug($setSlug);
        $oldValues = $this->repository->read($postId)->values();
        $newValues = $oldValues;

        foreach ($values as $key => $value) {
            $definition = $this->registeredDefinition($key);
            $sanitized = $definition === null ? null : $this->types->get($definition->typeId())->sanitize($value);

            if ($definition !== null && $sanitized !== null) {
                $newValues[$definition->key()] = $sanitized;
            }
        }

        if ($newValues === $oldValues) {
            if ($setSlug !== null) {
                $this->assignSet($postId, $setSlug);
            }

            return $newValues;
        }

        $this->write($postId, $setSlug ?? $this->repository->assignedSet($postId), $newValues);
        do_action(self::SAVED_ACTION, $postId, $newValues, $oldValues);

        return $newValues;
    }

    /**
     * @param array<string, mixed> $values
     */
    private function write(int $postId, ?string $setSlug, array $values): void
    {
        $previousSetSlug = $this->repository->assignedSet($postId);

        $this->assignSet($postId, $setSlug);

        if (!$this->repository->replace($postId, ProductValueDocument::fromValues($values))) {
            $this->repository->assignSet($postId, $previousSetSlug);

            throw new RuntimeException(__('The specification values could not be saved.', 'product-specifications'));
        }
    }

    private function assignSet(int $postId, ?string $setSlug): void
    {
        if (!$this->repository->assignSet($postId, $setSlug)) {
            throw new RuntimeException(__('The specification set could not be saved.', 'product-specifications'));
        }
    }

    private function productType(int $postId): string
    {
        $postType = get_post_type($postId);

        if ($postType !== 'product' && $postType !== 'product_variation') {
            throw new RuntimeException(__('Specification values require a product.', 'product-specifications'));
        }

        return $postType;
    }

    private function storedSetSlug(string $setSlug): string
    {
        $set = $this->sets->bySlug($setSlug);

        if ($set === null) {
            throw new RuntimeException(__('The selected specification set does not exist.', 'product-specifications'));
        }

        return $set->slug() !== '' ? $set->slug() : $setSlug;
    }

    private function registeredDefinition(string|int $key): ?SpecificationDefinition
    {
        if (!is_string($key) || $key === '') {
            return null;
        }

        $definition = $this->definitions->findByKey($key);

        return $definition !== null && $this->types->has($definition->typeId()) ? $definition : null;
    }

    /**
     * @param array<string, mixed> $submittedValues
     * @return array<string, mixed>
     */
    private function sanitizedReplacement(array $submittedValues, bool $isVariation): array
    {
        $values = [];

        foreach ($submittedValues as $key => $value) {
            if (!is_string($key) || $key === '') {
                continue;
            }

            $definition = $this->definitions->findByKey($key);

            if ($definition === null) {
                continue;
            }

            // The type's plugin may only be inactive, and reactivating it restores the value.
            if (!$this->types->has($definition->typeId())) {
                $values[$key] = $value;
                continue;
            }

            $sanitized = $this->types->get($definition->typeId())->sanitize($value);

            if ($sanitized !== null || $isVariation) {
                $values[$key] = $sanitized;
            }
        }

        return $values;
    }
}
