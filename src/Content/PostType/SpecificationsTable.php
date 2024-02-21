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
            'exclude_from_search'  => true,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => 'dw-specs', // 'admin.php?page=dw-specs'
            'query_var' => false,
            'rewrite' => array( 'slug' => 'specs-table' ),
            'capability_type' => 'post',
            'has_archive' => false,
            'exclude_from_search'  => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array( 'title' ),
        ];
    }

    public function key(): string
    {
        return self::KEY;
    }

    private static function labels(): array
    {
        return [
            'name' => esc_html__(
                'Specifications tables',
                'product-specifications'
            ),
            'singular_name' => esc_html__(
                'Specifications table',
                'product-specifications'
            ),
            'menu_name' => esc_html__(
                'Specifications table',
                'product-specifications'
            ),
            'name_admin_bar' => esc_html__(
                'Specifications tables',
                'product-specifications'
            ),
            'add_new' => esc_html__(
                'Add New',
                'product-specifications'
            ),
            'add_new_item' => esc_html__(
                'Add New Spec. table',
                'product-specifications'
            ),
            'new_item' => esc_html__(
                'New Spec. table',
                'product-specifications'
            ),
            'edit_item' => esc_html__(
                'Edit Spec. table',
                'product-specifications'
            ),
            'view_item' => esc_html__(
                'View Spec. table',
                'product-specifications'
            ),
            'all_items' => esc_html__(
                'Specification tables',
                'product-specifications'
            ),
            'search_items' => esc_html__(
                'Search Spec. tables',
                'product-specifications'
            ),
            'parent_item_colon' => esc_html__(
                'Parent Spec. tables:',
                'product-specifications'
            ),
            'not_found' => esc_html__(
                'No Spec. tables found.',
                'product-specifications'
            ),
            'not_found_in_trash' => esc_html__(
                'No Spec. tables found in Trash.',
                'product-specifications'
            ),
            'featured_image' => esc_html__(
                'Spec. table Cover Image',
                'product-specifications'
            ),
            'set_featured_image' => esc_html__(
                'Set cover image',
                'product-specifications'
            ),
            'remove_featured_image' => esc_html__(
                'Remove cover image', 'product-specifications'
            ),
            'use_featured_image' => esc_html__(
                'Use as cover image',
                'product-specifications'
            ),
            'archives' => esc_html__(
                'Spec. tables archives',
                'product-specifications'
            ),
            'insert_into_item' => esc_html__(
                'Insert into Spec. table',
                'product-specifications'
            ),
            'uploaded_to_this_item' => esc_html__(
                'Uploaded to this Spec. table',
                'product-specifications'
            ),
            'filter_items_list' => esc_html__(
                'Filter Spec. tables list',
                'product-specifications'
            ),
            'items_list_navigation' => esc_html__(
                'Spec. tables list navigation',
                'product-specifications'
            ),
            'items_list' => esc_html__(
                'Spec. tables list',
                'product-specifications'
            ),
        ];
    }
}
