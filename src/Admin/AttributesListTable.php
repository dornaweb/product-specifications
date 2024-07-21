<?php

namespace Amiut\ProductSpecs\Admin;

use Amiut\ProductSpecs\Template\TemplateRenderer;

class AttributesListTable implements AdminPageView
{
    private TemplateRenderer $renderer;

    public function __construct(TemplateRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function render(): string
    {
        return $this->renderer->render('');
    }
}
