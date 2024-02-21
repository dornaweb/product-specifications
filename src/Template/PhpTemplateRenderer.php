<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Template;

class PhpTemplateRenderer implements TemplateRenderer
{
    public function __construct(private Context $context)
    {
    }

    public function render(string $name, array $data = []): string
    {
        $template = new Template(
            $this->context,
            $name,
            $data,
        );

        return $template->render();
    }
}
