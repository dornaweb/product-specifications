<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Shortcode;

use Amiut\ProductSpecs\Repository\SpecificationsTableRepository;
use Amiut\ProductSpecs\Template\TemplateRenderer;
use Amiut\ProductSpecs\Upgrade\MigrationState;

class SpecificationsTable
{
    public const KEY = 'specs-table';

    private TemplateRenderer $renderer;
    private SpecificationsTableRepository $repository;

    public function __construct(
        TemplateRenderer $renderer,
        SpecificationsTableRepository $repository,
        private readonly MigrationState $migration
    ) {

        $this->renderer = $renderer;
        $this->repository = $repository;
    }

    /**
     * @param array<string>|string $shortcodeAttributes
     */
    public function render($shortcodeAttributes): string
    {
        if ($this->migration->requiresMigration()) {
            return '';
        }

        $shortcodeAttributes = is_array($shortcodeAttributes) ? $shortcodeAttributes : [];
        $postId = (int) ($shortcodeAttributes['post_id'] ?? 0);

        if (!$postId) {
            $postId = get_the_ID();
        }

        if ($postId < 1) {
            return '';
        }

        $table = $this->groupsWithSpecifications(
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

    /**
     * @param array<mixed> $groups
     * @return array<mixed>
     */
    private function groupsWithSpecifications(array $groups): array
    {
        return array_filter(
            $groups,
            /**
             * @param ?array $value
             */
            static fn ($value) =>
                isset($value['attributes']) && is_array($value['attributes'])
                    && count($value['attributes']) > 0
        );
    }
}
