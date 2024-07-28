<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Repository;

use Amiut\ProductSpecs\Content\Taxonomy;
use WP_Error;
use WP_Term;

/**
 * @psalm-type CollectionArguments = array{
 *      search_query?: string,
 *      group_id?: int,
 *      per_page?: int,
 *      current_page?: int
 * }
 * @psalm-import-type TermCollectionArguments from EntityCollectionFactory
 */
class AttributesRepository
{
    private EntityCollectionFactory $factory;

    public function __construct(EntityCollectionFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @psalm-param CollectionArguments $args
     * @return EntityCollection<WP_Term>
     */
    public function findCollection(array $args): EntityCollection
    {
        return $this->factory->termsCollection($this->prepareArguments($args));
    }

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

    /**
     * @param CollectionArguments $args
     * @return TermCollectionArguments
     */
    private function prepareArguments(array $args): array
    {
        /** @var TermCollectionArguments $preparedArguments */
        $preparedArguments = [
            'taxonomy' => Taxonomy\Attribute::KEY,
            'hide_empty' => false,
            'orderby' => 'term_id',
            'order' => 'DESC',
        ];

        if (!empty($args['search_query'])) {
            $preparedArguments['search'] = $args['search_query'];
        }

        if (!empty($args['group_id'])) {
            $preparedArguments = $preparedArguments + [
                    'meta_key' => 'attr_group',
                    'meta_value' => (string) $args['group_id'],
                ];
        }

        if (!empty($args['per_page'])) {
            $preparedArguments['per_page'] = $args['per_page'];
        }

        if (!empty($args['current_page'])) {
            $preparedArguments['current_page'] = $args['current_page'];
        }

        return $preparedArguments;
    }
}
