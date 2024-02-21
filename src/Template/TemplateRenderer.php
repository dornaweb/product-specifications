<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Template;

interface TemplateRenderer
{
    public function render(string $name, array $data = []): string;
}
