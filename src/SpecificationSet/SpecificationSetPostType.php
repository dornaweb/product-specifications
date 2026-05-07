<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet;

class SpecificationSetPostType
{
    public const KEY = 'specs-table';

    public function init(): void
    {
        register_post_type(
            self::KEY,
            $this->args()
        );
    }

    /**
     * @return array{
     * labels: array<string, string>,
     * exclude_from_search: bool,
     * public: bool,
     * publicly_queryable: bool,
     * show_in_nav_menus: bool,
     * show_ui: bool,
     * query_var: bool,
     * has_archive: bool,
     * show_in_rest: bool,
     * rest_base: string,
     * }
     */
    public function args(): array
    {
        return [
            'labels' => self::labels(),
            'exclude_from_search' => true,
            'public' => false,
            'publicly_queryable' => false,
            'show_in_nav_menus' => false,
            'show_ui' => false,
            'query_var' => false,
            'has_archive' => false,
            'show_in_rest' => true,
            'rest_base' => 'specification-sets',
        ];
    }

    /**
     * @return array<string, string>
     */
    // phpcs:ignore Inpsyde.CodeQuality.FunctionLength.TooLong
    private static function labels(): array
    {
        return [
            'name' => esc_html__(
                'Specifications Sets',
                'product-specifications'
            ),
            'singular_name' => esc_html__(
                'Specifications Set',
                'product-specifications'
            ),
            'menu_name' => esc_html__(
                'Specifications Sets',
                'product-specifications'
            ),
            'name_admin_bar' => esc_html__(
                'Specifications Sets',
                'product-specifications'
            ),
            'add_new' => esc_html__(
                'Add New',
                'product-specifications'
            ),
            'add_new_item' => esc_html__(
                'Add New Specification Set',
                'product-specifications'
            ),
            'new_item' => esc_html__(
                'New Specification Set',
                'product-specifications'
            ),
            'edit_item' => esc_html__(
                'Edit Specification Set',
                'product-specifications'
            ),
            'view_item' => esc_html__(
                'View Specification Set',
                'product-specifications'
            ),
            'all_items' => esc_html__(
                'Specification Sets',
                'product-specifications'
            ),
            'search_items' => esc_html__(
                'Search Specification Sets',
                'product-specifications'
            ),
            'parent_item_colon' => esc_html__(
                'Parent Specification Sets:',
                'product-specifications'
            ),
            'not_found' => esc_html__(
                'No Specification Sets found.',
                'product-specifications'
            ),
            'not_found_in_trash' => esc_html__(
                'No Specification Sets found in Trash.',
                'product-specifications'
            ),
            'featured_image' => esc_html__(
                'Specification Set Cover Image',
                'product-specifications'
            ),
            'set_featured_image' => esc_html__(
                'Set cover image',
                'product-specifications'
            ),
            'remove_featured_image' => esc_html__(
                'Remove cover image',
                'product-specifications'
            ),
            'use_featured_image' => esc_html__(
                'Use as cover image',
                'product-specifications'
            ),
            'archives' => esc_html__(
                'Specification Sets archives',
                'product-specifications'
            ),
            'insert_into_item' => esc_html__(
                'Insert into Specification Set',
                'product-specifications'
            ),
            'uploaded_to_this_item' => esc_html__(
                'Uploaded to this Specification Set',
                'product-specifications'
            ),
            'filter_items_list' => esc_html__(
                'Filter Specification Sets list',
                'product-specifications'
            ),
            'items_list_navigation' => esc_html__(
                'Specification Sets list navigation',
                'product-specifications'
            ),
            'items_list' => esc_html__(
                'Specification Sets list',
                'product-specifications'
            ),
        ];
    }
}
