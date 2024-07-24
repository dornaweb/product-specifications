<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Settings;

use Amiut\ProductSpecs\Template\TemplateRenderer;

final class SettingsPage
{
    private TemplateRenderer $renderer;

    public function __construct(TemplateRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function render(): void
    {
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $this->renderer->render(
            'admin/settings',
        );
    }
}
