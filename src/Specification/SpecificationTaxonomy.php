<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification;

final class SpecificationTaxonomy
{
    public const KEY = 'spec-attr';

    public function key(): string
    {
        return self::KEY;
    }

    public const REST_BASE = 'specifications';

    /**
     * A non-public taxonomy still answers REST once show_in_rest is on, and the
     * capabilities below are what decides who reaches it.
     *
     * @return array{
     *     show_in_menu: bool,
     *     show_in_nav_menus: bool,
     *     hierarchical: bool,
     *     labels: array<string, string>,
     *     show_ui: bool,
     *     query_var: bool,
     *     public: bool,
     *     rewrite: bool,
     *     show_in_rest: bool,
     *     rest_base: string,
     *     capabilities: array{
     *         manage_terms: string,
     *         edit_terms: string,
     *         delete_terms: string,
     *         assign_terms: string
     *     }
     * }
     */
    public function args(string $capability): array
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
            'show_in_rest' => true,
            'rest_base' => self::REST_BASE,
            'capabilities' => [
                'manage_terms' => $capability,
                'edit_terms' => $capability,
                'delete_terms' => $capability,
                'assign_terms' => $capability,
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            'name' => _x('Specifications', 'taxonomy general name', 'product-specifications'),
            'singular_name' => _x('Specification', 'taxonomy singular name', 'product-specifications'),
            'search_items' => __('Search specifications', 'product-specifications'),
            'all_items' => __('All specifications', 'product-specifications'),
            'parent_item' => __('Parent specification', 'product-specifications'),
            'parent_item_colon' => __('Parent specification:', 'product-specifications'),
            'edit_item' => __('Edit specification', 'product-specifications'),
            'update_item' => __('Update specification', 'product-specifications'),
            'add_new_item' => __('Add a new specification', 'product-specifications'),
            'new_item_name' => __('New specification', 'product-specifications'),
        ];
    }
}
