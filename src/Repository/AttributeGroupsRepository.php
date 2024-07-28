<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Repository;

use Amiut\ProductSpecs\Content\Taxonomy;
use WP_Error;
use WP_Term;

/**
 * @psalm-type CollectionArguments = array{
 *      search_query?: string,
 *      per_page?: int,
 *      current_page?: int
 * }
 * @psalm-import-type TermCollectionArguments from EntityCollectionFactory
 */
class AttributeGroupsRepository
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
     * @param CollectionArguments $args
     * @return TermCollectionArguments
     */
    private function prepareArguments(array $args): array
    {
        /** @var TermCollectionArguments $preparedArguments */
        $preparedArguments = [
            'taxonomy' => Taxonomy\AttributeGroup::KEY,
            'hide_empty' => false,
            'orderby' => 'term_id',
            'order' => 'DESC',
        ];

        if (!empty($args['search_query'])) {
            $preparedArguments['search'] = $args['search_query'];
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
