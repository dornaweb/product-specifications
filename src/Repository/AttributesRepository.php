<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Repository;

use Amiut\ProductSpecs\Content\Taxonomy;
use WP_Error;
use WP_Term;

class AttributesRepository
{
    /**
     * @return array<WP_Term>
     */
    public function findByGroupSorted(int $groupId): array
    {
        /**
         * @var array<int> $orderedAttributesIds
         */
        $orderedAttributesIds = array_map(
            'absint',
            array_filter((array) get_term_meta($groupId, 'attributes', true))
        );

        $terms = $this->findByGroup($groupId);

        usort(
            $terms,
            static function (WP_Term $attribute1, WP_Term $attribute2) use ($orderedAttributesIds) {
                $position1 = array_search($attribute1->term_id, $orderedAttributesIds, true);
                $position2 = array_search($attribute2->term_id, $orderedAttributesIds, true);

                if ($position1 === false && $position2 === false) {
                    return $attribute1->name <=> $attribute2->name;
                }

                if (is_int($position1) && is_int($position2)) {
                    return $position1 <=> $position2;
                }

                return $position1 === false ? 1 : -1;
            }
        );

        return $terms;
    }

    /**
     * @return array<WP_Term>
     */
    public function findByGroup(int $groupId): array
    {
        /** @var array<WP_Term>|WP_Error $terms */
        $terms = get_terms(
            [
                'taxonomy' => Taxonomy\Attribute::KEY,
                'hide_empty' => false,
                'meta_key' => 'attr_group',
                'meta_value' => (string) $groupId,
            ]
        );

        if ($terms instanceof WP_Error) {
            return [];
        }

        return $terms;
    }
}
