<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ProductSpecifications;

use Amiut\ProductSpecs\Repository\AttributeFieldRepository;
use Amiut\ProductSpecs\Repository\SpecificationsTableRepository;
use Amiut\ProductSpecs\Template\TemplateRenderer;
use WP_Post;

final class AjaxTablesHandler
{
    public const ACTION_LOAD_TABLE = 'dwps_load_table';

    private TemplateRenderer $renderer;
    private AttributeFieldRepository $repository;

    public function __construct(TemplateRenderer $renderer, AttributeFieldRepository $repository)
    {
        $this->renderer = $renderer;
        $this->repository = $repository;
    }

    /**
     * @return never
     */
    public function loadTable(): void
    {
        $tableId = (int) filter_input(INPUT_POST, 'specs_table', FILTER_VALIDATE_INT);
        $postId = (int) filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
        $post = get_post($postId);

        if ($tableId < 1) {
            wp_send_json_error('Invalid table ID');
        }

        if (! $post instanceof WP_Post) {
            wp_send_json_error('Invalid post ID');
        }

        $output = $this->renderer->render(
            'admin/metabox/product-specs-table/product-attribute-fields',
            [
                'groupedCollection' => $this->repository->findGroupedCollection($tableId, $post->ID),
            ]
        );

        wp_send_json_success($output);
    }
}
