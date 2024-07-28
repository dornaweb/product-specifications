<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Content\Taxonomy;

final class Attribute
{
    public const KEY = 'spec-attr';

    public function key(): string
    {
        return self::KEY;
    }

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

    public static function labels(): array
    {
        return [
            'name' => _x('Attributes', 'taxonomy general name', 'product-specifications'),
            'singular_name' => _x('attribute', 'taxonomy singular name', 'product-specifications'),
            'search_items' => __('Search in attributes', 'product-specifications'),
            'all_items' => __('all attributes', 'product-specifications'),
            'parent_item' => __('parent attribute', 'product-specifications'),
            'parent_item_colon' => __('parent attribute : ', 'product-specifications'),
            'edit_item' => __('Edit attribute', 'product-specifications'),
            'update_item' => __('Update attribute', 'product-specifications'),
            'add_new_item' => __('Add a new attribute', 'product-specifications'),
            'new_item_name' => __('New attribute', 'product-specifications'),
        ];
    }
}
