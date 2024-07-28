<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Repository;

use WP_Error;
use WP_Term;

/**
 * @psalm-type TermCollectionArguments = array {
 *     taxonomy: string,
 *     search?: string,
 *     hide_empty?: bool,
 *     meta_key?: string,
 *     meta_value?: mixed,
 *     per_page?: int,
 *     current_page?: int
 * }
 */
class EntityCollectionFactory
{
    private const DEFAULT_PER_PAGE = 1;

    /**
     * @psalm-param TermCollectionArguments $args
     */
    public function termsCollection(array $args): EntityCollection
    {
        $perPage = $args['per_page'] ?? self::DEFAULT_PER_PAGE;
        $currentPage = $args['current_page'] ?? 1;

        $args['number'] = $perPage;
        $args['offset'] = ($currentPage - 1) * $perPage;
        unset($args['per_page']);
        unset($args['current_page']);

        $terms = get_terms($args);

        if ($terms instanceof WP_Error) {
            return EntityCollection::empty();
        }

        $totalCount = $this->countWpTerms($args);

        /** @var EntityCollection<WP_Term> $collection */
        $collection = new EntityCollection($terms, $totalCount, $perPage, $currentPage);

        return $collection;
    }

    private function countWpTerms(array $args): int
    {
        $args['number'] = 0;

        if (isset($args['offset'])) {
            unset($args['offset']);
        }

        $count = wp_count_terms($args);

        if ($count instanceof WP_Error) {
            return 0;
        }

        return (int) $count;
    }
}
