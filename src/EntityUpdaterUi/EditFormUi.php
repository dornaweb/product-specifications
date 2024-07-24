<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\EntityUpdaterUi;

use Amiut\ProductSpecs\Template\TemplateRenderer;

final class EditFormUi
{
    private TemplateRenderer $renderer;

    public function __construct(TemplateRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function render(): void
    {
        $id = (int) filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $type = (string) filter_input(INPUT_GET, 'type', FILTER_SANITIZE_SPECIAL_CHARS);

        if ($id < 1) {
            die;
        }

        if ($type === 'attribute') {
            // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $this->renderer->render(
                'admin/entity-management/edit-attribute-form',
                [
                    'id' => $id,
                ]
            );
            // phpcs:enable

            die;
        }

        // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $this->renderer->render(
            'admin/entity-management/edit-group-form',
            [
                'id' => $id,
            ]
        );
        // phpcs:enable
        die;
    }
}
