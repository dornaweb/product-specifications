<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\EntityUpdaterUi;

use Amiut\ProductSpecs\Repository\AttributesRepository;
use Amiut\ProductSpecs\Template\TemplateRenderer;

final class GroupReArrangeFormUi
{
    private TemplateRenderer $renderer;
    private AttributesRepository $repository;

    public function __construct(TemplateRenderer $renderer, AttributesRepository $repository)
    {
        $this->renderer = $renderer;
        $this->repository = $repository;
    }

    public function render(): void
    {
        $id = (int) filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if ($id < 1) {
            return;
        }

        $attributes = $this->repository->findByGroupSorted($id);

        // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $this->renderer->render(
            'admin/entity-management/group-rearrange-form',
            [
                'id' => $id,
                'attributes' => $attributes,
            ]
        );
        // phpcs:enable
        die;
    }
}
