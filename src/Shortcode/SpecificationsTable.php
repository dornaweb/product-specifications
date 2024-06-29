<?php

declare(strict_types=1);

/**
 * Product Specifications main class
 *
 * @package Amiut\ProductSpecs
 * @since   0.1
 */

namespace Amiut\ProductSpecs\Shortcode;

use Amiut\ProductSpecs\Repository\SpecificationsTableRepository;
use Amiut\ProductSpecs\Template\TemplateRenderer;

defined('ABSPATH') || exit;

class SpecificationsTable
{
    public const KEY = 'specs-table';

    private TemplateRenderer $renderer;
    private SpecificationsTableRepository $repository;

    public function __construct(
        TemplateRenderer $renderer,
        SpecificationsTableRepository $repository
    ) {

        $this->renderer = $renderer;
        $this->repository = $repository;
    }

    /**
     * @param array|string $attributes
     */
    public function render($attributes): string
    {
        $attributes = is_array($attributes) ? $attributes : [];
        $postId = (int) ($attributes['post_id'] ?? 0);

        if (!$postId) {
            $postId = get_the_ID();
        }

        if ($postId < 1) {
            return '';
        }

        $table = $this->specificationsTableWithNonEmptyAttributes(
            $this->repository->findByProductId($postId)
        );

        if (!$table) {
            return '';
        }

        return (string) apply_filters(
            'dw_specs_table_shortcode_output',
            $this->renderer->render(
                'shortcodes/specifications-table',
                [
                    'table' => $table,
                ]
            )
        );
    }

    private function specificationsTableWithNonEmptyAttributes(array $attributes): array
    {
        return array_filter(
            $attributes,
            /**
             * @param ?array $value
             */
            static fn ($value) =>
                isset($value['attributes']) && is_array($value['attributes'])
                    && count($value['attributes']) > 0
        );
    }
}
