<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Content\PostType;

final class SpecificationsTable
{
    public const KEY = 'specs-table';

    public function args(): array
    {
        return [
            'labels' => self::labels(),
            'capability_type' => 'post',
            'exclude_from_search' => true,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => 'dw-specs', // 'admin.php?page=dw-specs'
            'query_var' => false,
            'rewrite' => ['slug' => 'specs-table'],
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => ['title'],
        ];
    }

    public function key(): string
    {
        return self::KEY;
    }

    // phpcs:ignore Inpsyde.CodeQuality.FunctionLength.TooLong
    private static function labels(): array
    {
        return [
            'name' => esc_html__(
                'Specifications bundle',
                'product-specifications'
            ),
            'singular_name' => esc_html__(
                'Specifications bundle',
                'product-specifications'
            ),
            'menu_name' => esc_html__(
                'Specifications bundle',
                'product-specifications'
            ),
            'name_admin_bar' => esc_html__(
                'Specifications bundle',
                'product-specifications'
            ),
            'add_new' => esc_html__(
                'Add New',
                'product-specifications'
            ),
            'add_new_item' => esc_html__(
                'Add New bundle',
                'product-specifications'
            ),
            'new_item' => esc_html__(
                'New specification bundle',
                'product-specifications'
            ),
            'edit_item' => esc_html__(
                'Edit bundle',
                'product-specifications'
            ),
            'view_item' => esc_html__(
                'View bundle',
                'product-specifications'
            ),
            'all_items' => esc_html__(
                'Specification bundles',
                'product-specifications'
            ),
            'search_items' => esc_html__(
                'Search bundle',
                'product-specifications'
            ),
            'parent_item_colon' => esc_html__(
                'Parent bundle:',
                'product-specifications'
            ),
            'not_found' => esc_html__(
                'No bundle found.',
                'product-specifications'
            ),
            'not_found_in_trash' => esc_html__(
                'No bundle found in Trash.',
                'product-specifications'
            ),
            'featured_image' => esc_html__(
                'set Cover Image',
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
                'bundle archives',
                'product-specifications'
            ),
            'insert_into_item' => esc_html__(
                'Insert into bundle',
                'product-specifications'
            ),
            'uploaded_to_this_item' => esc_html__(
                'Uploaded to this bundle',
                'product-specifications'
            ),
            'filter_items_list' => esc_html__(
                'Filter bundle list',
                'product-specifications'
            ),
            'items_list_navigation' => esc_html__(
                'bundle list navigation',
                'product-specifications'
            ),
            'items_list' => esc_html__(
                'bundle list',
                'product-specifications'
            ),
        ];
    }
}
