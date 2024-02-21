<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Template;

use Throwable;

class Template
{
    public function __construct(
        private Context $context,
        private string $name,
        private array $data = []
    ) {
    }

    public function data(): array
    {
        return $this->data;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function render(): string
    {
        try {
            $level = ob_get_level();
            $path = $this->context->resolveTemplatePath($this->name());
            ob_start();

            $templateData = $this->data();

            (static function (string $path) use ($templateData) {
                $data = $templateData;
                include $path;
            })($path);

            return ob_get_clean();
        } catch (Throwable $throwable) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            throw $throwable;
        }
    }
}
