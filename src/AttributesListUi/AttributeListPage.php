<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\AttributesListUi;

use Amiut\ProductSpecs\Template\TemplateRenderer;

final class AttributeListPage
{
    private TemplateRenderer $renderer;

    public function __construct(TemplateRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function render(): void
    {
        echo $this->renderer->render(
            'admin/attributes/list-page'
        );
    }
}
