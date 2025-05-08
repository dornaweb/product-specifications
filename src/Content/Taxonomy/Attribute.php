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
            'name' => _x('Specifications', 'taxonomy general name', 'product-specifications'),
            'singular_name' => _x('specification', 'taxonomy singular name', 'product-specifications'),
            'search_items' => __('Search in specifications', 'product-specifications'),
            'all_items' => __('all specifications', 'product-specifications'),
            'parent_item' => __('parent specification', 'product-specifications'),
            'parent_item_colon' => __('parent specification : ', 'product-specifications'),
            'edit_item' => __('Edit specification', 'product-specifications'),
            'update_item' => __('Update specification', 'product-specifications'),
            'add_new_item' => __('Add a new specification', 'product-specifications'),
            'new_item_name' => __('New specification', 'product-specifications'),
        ];
    }
}
