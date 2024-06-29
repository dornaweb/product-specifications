<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Content\Taxonomy;

final class AttributeGroup
{
    public const KEY = 'spec-group';

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
            'name' => _x('Groups', 'taxonomy general name', 'product-specifications'),
            'singular_name' => _x('group', 'taxonomy singular name', 'product-specifications'),
            'search_items' => __('Search in groups', 'product-specifications'),
            'all_items' => __('all groups', 'product-specifications'),
            'parent_item' => __('parent group', 'product-specifications'),
            'parent_item_colon' => __('parent group : ', 'product-specifications'),
            'edit_item' => __('Edit group', 'product-specifications'),
            'update_item' => __('Update group', 'product-specifications'),
            'add_new_item' => __('Add a new group', 'product-specifications'),
            'new_item_name' => __('New group', 'product-specifications'),
        ];
    }
}
