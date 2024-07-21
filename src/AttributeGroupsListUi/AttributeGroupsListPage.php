<?php

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
        echo $this->renderer->render(
            'admin/attribute-groups/list-page'
        );
    }
}
