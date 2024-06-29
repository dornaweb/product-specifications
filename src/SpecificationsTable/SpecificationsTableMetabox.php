<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationsTable;

use Amiut\ProductSpecs\Content\PostType;
use Amiut\ProductSpecs\Content\Taxonomy;
use Amiut\ProductSpecs\Metabox\Metabox;
use Amiut\ProductSpecs\Template\TemplateRenderer;
use WP_Post;
use WP_Term;

final class SpecificationsTableMetabox implements Metabox
{
    private TemplateRenderer $renderer;

    public function __construct(TemplateRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function enabled(WP_Post $post): bool
    {
        return $post->post_type === PostType\SpecificationsTable::KEY;
    }

    public function id(): string
    {
        return 'dwps-specs-table';
    }

    public function title(): string
    {
        return __('Table Options', 'product-specifications');
    }

    public function context(): string
    {
        return self::CONTEXT_NORMAL;
    }

    public function priority(): string
    {
        return self::PRIORITY_HIGH;
    }

    public function render(WP_Post $post): string
    {
        return $this->renderer->render(
            'admin/metabox/specifications-table/table-metabox',
            [
                'groups' => $this->groups(),
                'post' => $post,
                'selectedGroups' => $this->selectedGroups($post),
            ]
        );
    }

    /**
     * @return array<WP_Term>
     */
    private function selectedGroups(WP_Post $post): array
    {
        $selectedGroups = array_map(
            /**
             * @param int|string $groupId
             * @return ?WP_Term
             */
            static function ($groupId) {
                $term = get_term_by('id', (int) $groupId, Taxonomy\AttributeGroup::KEY);
                return $term instanceof WP_Term ? $term : null;
            },
            (array) get_post_meta($post->ID, '_groups', true)
        );

        return array_filter($selectedGroups);
    }

    /**
     * @return array<WP_Term>
     */
    private function groups(): array
    {
        /** @var array<WP_Term> */
        return get_terms([
            'taxonomy' => 'spec-group',
            'hide_empty' => false,
        ]);
    }

    public function action(WP_Post $post): void
    {
        // TODO: Evaluate possibility of checking table data against a filter schema using filter_input_array
        // phpcs:ignore WordPressVIPMinimum.Security.PHPFilterFunctions.RestrictedFilter
        $groups = (array) filter_input(
            INPUT_POST,
            'groups',
            FILTER_UNSAFE_RAW,
            FILTER_REQUIRE_ARRAY
        );

        update_post_meta($post->ID, '_groups', $groups);
    }
}
