<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Template;

class PhpTemplateRenderer implements TemplateRenderer
{
    private Context $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function render(string $name, array $data = []): string
    {
        $template = new Template(
            $this->context,
            $name,
        );

        $data = array_merge(
            $data,
            [
                'renderer' => $this,
            ]
        );

        return $template->render($data);
    }
}
