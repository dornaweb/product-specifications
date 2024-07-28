<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport;

use Amiut\ProductSpecs\Template\TemplateRenderer;

final class ImportExportPage
{
    private TemplateRenderer $renderer;

    public function __construct(TemplateRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function render(): void
    {
        $tables = new \WP_Query([
            'post_type' => 'spec-table',
            'showposts' => -1,
        ]);
        $tables = $tables->get_posts();
        wp_reset_postdata();

        // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $this->renderer->render(
            'admin/import-export',
            [
                'tables' => $tables,
            ]
        );
        // phpcs:enable
    }
}
