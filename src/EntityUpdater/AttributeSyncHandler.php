<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\EntityUpdater;

final class AttributeSyncHandler
{
    /**
     * @wp-hook product_specs.entities.attribute_deleted
     */
    public function whenDeleted(int $attributeId, int $groupId): void
    {
        $attributesOrderedIds = array_map(
            'absint',
            array_filter((array) get_term_meta($groupId, 'attributes', true))
        );

        if (count($attributesOrderedIds) < 1) {
            return;
        }

        if (! in_array($attributeId, $attributesOrderedIds, true)) {
            return;
        }

        unset($attributesOrderedIds[$attributeId]);
        $updatedAttributes = array_diff($attributesOrderedIds, [$attributeId]);
        update_term_meta($groupId, 'attributes', array_values($updatedAttributes));
    }

    /**
     * @wp-hook product_specs.entities.attribute_added
     * @wp-hook product_specs.entities.attribute_updated
     */
    public function whenAdded(int $attributeId): void
    {
        $groupId = (int) get_term_meta($attributeId, 'attr_group', true);

        $attributesOrderedIds = array_map(
            'absint',
            array_filter((array) get_term_meta($groupId, 'attributes', true))
        );

        if (in_array($attributeId, $attributesOrderedIds, true)) {
            return;
        }

        $attributesOrderedIds[] = $attributeId;

        update_term_meta($groupId, 'attributes', array_values($attributesOrderedIds));
    }
}
