<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Content\Taxonomy;

/**
 * The v1 grouping taxonomy. Groups are presentational and live inside a
 * specification set now, so this exists only until the migration has read it.
 */
final class LegacyGroupTaxonomy
{
    public const KEY = 'spec-group';

    public function key(): string
    {
        return self::KEY;
    }

    /**
     * @return array{
     * show_in_menu: bool,
     * show_in_nav_menus: bool,
     * hierarchical: bool,
     * labels: array<string, string>,
     * show_ui: bool,
     * query_var: bool,
     * public: bool,
     * rewrite: bool,
     * }
     */
    public function args(): array
    {
        return [
            'show_in_menu' => false,
            'show_in_nav_menus' => false,
            'hierarchical' => false,
            'labels' => self::labels(),
            'show_ui' => false,
            'query_var' => false,
            'public' => false,
            'rewrite' => false,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            'name' => _x('Legacy groups', 'taxonomy general name', 'product-specifications'),
            'singular_name' => _x('Legacy group', 'taxonomy singular name', 'product-specifications'),
            'search_items' => __('Search legacy groups', 'product-specifications'),
            'all_items' => __('All legacy groups', 'product-specifications'),
            'parent_item' => __('Parent legacy group', 'product-specifications'),
            'parent_item_colon' => __('Parent legacy group:', 'product-specifications'),
            'edit_item' => __('Edit legacy group', 'product-specifications'),
            'update_item' => __('Update legacy group', 'product-specifications'),
            'add_new_item' => __('Add a new legacy group', 'product-specifications'),
            'new_item_name' => __('New legacy group', 'product-specifications'),
        ];
    }
}
