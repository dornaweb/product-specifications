<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Template;

use Throwable;

class Template
{
    private Context $context;
    private string $name;

    public function __construct(
        Context $context,
        string $name
    ) {

        $this->context = $context;
        $this->name = $name;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function render(array $data): string
    {
        try {
            $level = ob_get_level();
            $path = $this->context->resolveTemplatePath($this->name());
            ob_start();

            (static function (string $path) use ($data) {
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
