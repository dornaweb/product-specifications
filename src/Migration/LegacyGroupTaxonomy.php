<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration;

use Amiut\ProductSpecs\SpecificationSet\SpecificationSetPostType;

/**
 * WordPress refuses to read terms of an unregistered taxonomy, and the sets step
 * reads its groups from these terms. Nothing but a running migration registers it.
 */
final class LegacyGroupTaxonomy
{
    public const KEY = 'spec-group';

    public function register(): void
    {
        register_taxonomy(
            self::KEY,
            SpecificationSetPostType::KEY,
            [
                'public' => false,
                'show_ui' => false,
                'show_in_rest' => false,
                'query_var' => false,
                'rewrite' => false,
            ]
        );
    }
}
