<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Repository;

use Amiut\ProductSpecs\Attribute\AttributeField;
use Amiut\ProductSpecs\Attribute\AttributeFieldCollection;
use Amiut\ProductSpecs\Attribute\AttributeFieldFactory;
use Amiut\ProductSpecs\Attribute\AttributeFieldGroup;
use Amiut\ProductSpecs\Attribute\AttributeFieldGroupCollection;
use Amiut\ProductSpecs\Content\Taxonomy;
use WP_Term;

class AttributeFieldRepository
{
    private AttributeFieldFactory $attributeFieldFactory;
    private AttributesRepository $attributesRepository;

    public function __construct(
        AttributeFieldFactory $attributeFieldFactory,
        AttributesRepository $attributesRepository
    ) {

        $this->attributeFieldFactory = $attributeFieldFactory;
        $this->attributesRepository = $attributesRepository;
    }

    public function findGroupedCollection(int $tableId, ?int $postId = null): AttributeFieldGroupCollection
    {
        $collection = new AttributeFieldGroupCollection();
        $groupsTerms = $this->groupTerms($tableId);

        foreach ($groupsTerms as $groupTerm) {
            $attributes = $this->findAttributesByGroupId($groupTerm->term_id, $postId);
            $collection->append(
                new AttributeFieldGroup(
                    $groupTerm->name,
                    $groupTerm->slug,
                    $groupTerm->term_id,
                    $attributes
                )
            );
        }

        return $collection;
    }

    public function findAttributesByGroupId(int $groupId, ?int $postId = null): AttributeFieldCollection
    {
        $collection = new AttributeFieldCollection();
        $attributeTerms = $this->attributesRepository->findByGroupSorted($groupId);

        foreach ($attributeTerms as $attributeTerm) {
            $attributeField = $this->attributeFieldFactory->createFromWpTerm(
                $attributeTerm,
                $postId
            );

            if (is_null($attributeField)) {
                continue;
            }

            $collection->append($attributeField);
        }

        return $collection;
    }

    /**
     * @return array<WP_Term>
     */
    private function groupTerms(int $tableId): array
    {
        $result = [];

        $groups = array_filter((array) get_post_meta($tableId, '_groups', true));

        foreach ($groups as $groupId) {
            $group = get_term_by('id', (int) $groupId, Taxonomy\AttributeGroup::KEY);

            if ($group instanceof WP_Term) {
                $result[] = $group;
            }
        }

        return $result;
    }
}
