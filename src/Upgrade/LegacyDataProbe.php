<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Upgrade;

use Amiut\ProductSpecs\SpecificationSet\SpecificationSetPostType;

/**
 * Runs during activation, before the post type is registered, so posts are
 * queried by the post_type column rather than through the registered object.
 * `spec-attr` terms are deliberately not counted: get_terms() refuses an
 * unregistered taxonomy, and a site with specification terms but no tables has
 * nothing assembled to migrate.
 */
final class LegacyDataProbe
{
    private const LEGACY_OPTIONS = [
        'dwps_options',
        'dwps_tab_title',
        'dwps_view_per_page',
        'dwps_wc_default_specs',
        'dwps_disable_default_styles',
    ];

    public function found(): bool
    {
        return $this->hasSpecificationSetPosts() || $this->hasLegacyOptions();
    }

    private function hasSpecificationSetPosts(): bool
    {
        $posts = get_posts([
            'post_type' => SpecificationSetPostType::KEY,
            'post_status' => 'any',
            'numberposts' => 1,
            'fields' => 'ids',
            'no_found_rows' => true,
        ]);

        return is_array($posts) && $posts !== [];
    }

    private function hasLegacyOptions(): bool
    {
        foreach (self::LEGACY_OPTIONS as $option) {
            if (get_option($option, null) !== null) {
                return true;
            }
        }

        return false;
    }
}
