<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Rest;

use Amiut\ProductSpecs\Specification\SpecificationSlug;
use Amiut\ProductSpecs\Specification\SpecificationTaxonomy;
use stdClass;
use WP_REST_Request;

/**
 * The slug is the identity every stored value, set structure and export is
 * keyed by. Core's term controller writes whatever slug a request carries, so
 * the rule is enforced here, at the boundary every client passes through.
 */
final class SlugGuard
{
    public function register(): void
    {
        add_filter(
            'rest_pre_insert_' . SpecificationTaxonomy::KEY,
            [$this, 'freeze'],
            10,
            2
        );
    }

    public function freeze(stdClass $term, WP_REST_Request $request): stdClass
    {
        if (is_numeric($request['id'] ?? null)) {
            unset($term->slug);

            return $term;
        }

        $name = isset($term->name) && is_string($term->name) ? $term->name : '';
        $term->slug = SpecificationSlug::forName($name)->value();

        return $term;
    }
}
