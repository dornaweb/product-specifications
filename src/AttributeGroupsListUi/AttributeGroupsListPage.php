<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\AttributeGroupsListUi;

use Amiut\ProductSpecs\Template\TemplateRenderer;

final class AttributeGroupsListPage
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
            'admin/attribute-groups/list-page'
        );
    }
}
