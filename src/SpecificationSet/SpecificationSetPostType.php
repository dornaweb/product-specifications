<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet;

use Amiut\ProductSpecs\Capability\Capabilities;

class SpecificationSetPostType
{
    public const KEY = 'specs-table';

    public function __construct(private readonly Capabilities $capabilities)
    {
    }

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
     * supports: list<string>,
     * capabilities: array<string, string>,
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
            'supports' => ['title', 'editor', 'custom-fields'],
            'capabilities' => $this->capabilities(),
        ];
    }

    /**
     * Every key get_post_type_capabilities() consults, spelled out. Omitting one
     * silently falls back to a stock post capability, which lets a default
     * Editor reach these posts.
     *
     * @return array<string, string>
     */
    private function capabilities(): array
    {
        $capability = $this->capabilities->manageSpecifications();

        return [
            'edit_post' => $capability,
            'read_post' => $capability,
            'delete_post' => $capability,
            'edit_posts' => $capability,
            'edit_others_posts' => $capability,
            'delete_posts' => $capability,
            'publish_posts' => $capability,
            'read_private_posts' => $capability,
            'create_posts' => $capability,
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function labels(): array
    {
        return [
            'name' => esc_html__('Specification sets', 'product-specifications'),
            'singular_name' => esc_html__('Specification set', 'product-specifications'),
        ];
    }
}
