<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Template;

use RuntimeException;

class Context
{
    private array $directories = [];

    public function __construct(array $directories)
    {
        foreach ($directories as $directory) {
            $this->addDirectory($directory);
        }
    }

    public function resolveTemplatePath(string $template): string
    {
        $template = rtrim($template, '.php');

        foreach ($this->directories as $directory) {
            $path = $directory . DIRECTORY_SEPARATOR . $template . '.php';

            if (file_exists($path)) {
                return $path;
            }
        }

        throw new TemplateNotFoundException(
            sprintf(
                'Template "%s" not found',
                esc_html($template)
            ),
        );
    }

    private function addDirectory(string $directoryPath): void
    {
        $path = $this->normalizePath($directoryPath);

        if (in_array($directoryPath, $this->directories, true)) {
            return;
        }

        if (!is_dir($path)) {
            throw new RuntimeException(
                sprintf(
                    'Directory "%s" does not exist',
                    $path
                ),
            );
        }

        $this->directories[] = $path;
    }

    private function normalizePath(string $directoryPath): string
    {
        return rtrim($directoryPath, '/');
    }
}
