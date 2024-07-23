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
        $groupAttributes = array_map(
            'absint',
            array_filter((array) get_term_meta($groupId, 'attributes', true))
        );

        if (count($groupAttributes) < 1) {
            return;
        }

        if (! in_array($attributeId, $groupAttributes, true)) {
            return;
        }

        unset($groupAttributes[ $attributeId ]);
        $updatedAttributes = array_diff($groupAttributes, [$attributeId]);
        update_term_meta($groupId, 'attributes', $updatedAttributes);
    }

    /**
     * @wp-hook product_specs.entities.attribute_added
     * @wp-hook product_specs.entities.attribute_updated
     */
    public function whenAdded(int $attributeId): void
    {
        $groupId = (int) get_term_meta($attributeId, 'attr_group', true);

        $groupAttributes = array_map(
            'absint',
            array_filter((array) get_term_meta($groupId, 'attributes', true))
        );

        if (count($groupAttributes) < 1) {
            return;
        }

        if (! in_array($attributeId, $groupAttributes, true)) {
            return;
        }

        $groupAttributes[] = $attributeId;

        update_term_meta($groupId, 'attributes', $groupAttributes);
    }
}
