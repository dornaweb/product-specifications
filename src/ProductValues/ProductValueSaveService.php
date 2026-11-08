<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ProductValues;

use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetRepository;
use RuntimeException;

final class ProductValueSaveService
{
    public function __construct(
        private readonly ProductValueRepository $repository,
        private readonly SpecificationDefinitionRepository $definitions,
        private readonly SpecificationTypeRegistry $types,
        private readonly SpecificationSetRepository $sets
    ) {
    }

    /**
     * @param array<string, mixed> $submittedValues
     * @return array<string, mixed>
     */
    public function save(int $postId, ?string $setSlug, array $submittedValues): array
    {
        $postType = get_post_type($postId);

        if ($postType !== 'product' && $postType !== 'product_variation') {
            throw new RuntimeException(__('Specification values require a product.', 'product-specifications'));
        }

        if ($setSlug !== null) {
            $set = $this->sets->bySlug($setSlug);

            if ($set === null) {
                throw new RuntimeException(
                    __('The selected specification set does not exist.', 'product-specifications')
                );
            }

            $setSlug = $set->slug() !== '' ? $set->slug() : $setSlug;
        }

        $oldValues = $this->repository->read($postId)->values();
        $previousSetSlug = $this->repository->assignedSet($postId);

        if ($setSlug === null) {
            $newValues = [];
        } else {
            $newValues = $this->sanitizeValues(
                $submittedValues,
                $postType === 'product_variation'
            );
        }

        if (!$this->repository->assignSet($postId, $setSlug)) {
            throw new RuntimeException(__('The specification set could not be saved.', 'product-specifications'));
        }

        if (!$this->repository->replace($postId, ProductValueDocument::fromValues($newValues))) {
            $this->repository->assignSet($postId, $previousSetSlug);

            throw new RuntimeException(__('The specification values could not be saved.', 'product-specifications'));
        }

        do_action('product_specifications_values_saved', $postId, $newValues, $oldValues);

        return $newValues;
    }

    /**
     * @param array<string, mixed> $submittedValues
     * @return array<string, mixed>
     */
    private function sanitizeValues(array $submittedValues, bool $isVariation): array
    {
        $values = [];

        foreach ($submittedValues as $key => $value) {
            if (!is_string($key) || $key === '') {
                continue;
            }

            $definition = $this->definitions->findByKey($key);

            // Orphans must survive a full replace. They may belong to a type
            // extension that is temporarily inactive or a previously used set.
            if ($definition === null || !$this->types->has($definition->typeId())) {
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
