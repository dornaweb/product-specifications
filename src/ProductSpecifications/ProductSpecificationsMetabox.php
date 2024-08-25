<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ProductSpecifications;

use Amiut\ProductSpecs\Metabox\Metabox;
use Amiut\ProductSpecs\Repository\AttributeFieldRepository;
use Amiut\ProductSpecs\Template\TemplateRenderer;
use WP_Post;
use WP_Term;

final class ProductSpecificationsMetabox implements Metabox
{
    private TemplateRenderer $renderer;
    private AttributeFieldRepository $repository;

    public function __construct(
        TemplateRenderer $renderer,
        AttributeFieldRepository $repository
    ) {

        $this->renderer = $renderer;
        $this->repository = $repository;
    }

    public function enabled(WP_Post $post): bool
    {
        return $post->post_type === 'product';
    }

    public function id(): string
    {
        return 'dwps-specs-table';
    }

    public function title(): string
    {
        return __('Specification table', 'product-specifications');
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
        $currentTableId = (int) get_post_meta($post->ID, '_dwps_table', true);

        return $this->renderer->render(
            'admin/metabox/product-specs-table/edit',
            [
                'currentTableId' => $currentTableId,
                'tables' => $this->tables(),
                'post' => $post,
                'groupedCollection' => $this->repository->findGroupedCollection($currentTableId, $post->ID),
            ]
        );
    }

    /**
     * @return array<WP_Post>
     */
    private function tables(): array
    {
        /** @var WP_Post[] */
        return get_posts(
            [
                'post_type' => 'specs-table',
                'showposts' => -1,
            ]
        );
    }

    public function action(WP_Post $post): void
    {
        $tableId = (int) filter_input(INPUT_POST, 'specs_table', FILTER_VALIDATE_INT);

        if (!$tableId) {
            delete_post_meta($post->ID, '_dwps_specification_table');
            delete_post_meta($post->ID, '_dwps_table');
            return;
        }

        update_post_meta($post->ID, '_dwps_table', $tableId);

        if (!$tableId) {
            return;
        }

        // phpcs:ignore WordPressVIPMinimum.Security.PHPFilterFunctions.RestrictedFilter
        $tableValues = (array) filter_input(
            INPUT_POST,
            'dw-attr',
            FILTER_UNSAFE_RAW,
            FILTER_REQUIRE_ARRAY
        );

        update_post_meta(
            $post->ID,
            '_dwps_specification_table',
            $this->prepareSpecificationsTable($tableValues)
        );
    }

    private function prepareSpecificationsTable(array $specificationsTableData): array
    {
        $result = [];

        foreach ($specificationsTableData as $groupId => $groupAttributes) {
            if (!is_array($groupAttributes)) {
                continue;
            }

            $group = get_term_by('id', (int) $groupId, 'spec-group');

            if (!$group instanceof WP_Term) {
                continue;
            }

            $result[] = [
                'group_id' => $groupId,
                'group_name' => $group->name,
                'group_slug' => $group->slug,
                'group_desc' => $group->description,
                'attributes' => $this->attributesByGroup($groupAttributes),
            ];
        }

        return $result;
    }

    private function attributesByGroup(array $group): array
    {
        $result = [];

        foreach ($group as $attributeId => $value) {
            if (empty($value) || $value === 'dwspecs_chk_none') {
                continue;
            }

            $attribute = get_term_by('id', (int) $attributeId, 'spec-attr');

            if (!$attribute instanceof WP_Term) {
                continue;
            }

            $result[] = [
                'attr_id' => $attributeId,
                'attr_name' => $attribute->name,
                'attr_slug' => $attribute->slug,
                'attr_desc' => $attribute->description,
                'value' => trim((string) $value),
            ];
        }

        return $result;
    }
}
