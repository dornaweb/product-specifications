<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\EntityUpdaterUi;

use Amiut\ProductSpecs\EntityUpdater\AttributeController;
use Amiut\ProductSpecs\EntityUpdater\AttributeGroupController;
use Amiut\ProductSpecs\EntityUpdater\AttributeSyncHandler;
use Amiut\ProductSpecs\Template\TemplateRenderer;
use Inpsyde\Modularity\Module\ExecutableModule;
use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;
use Psr\Container\ContainerInterface;

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
            echo $this->renderer->render(
                'admin/entity-management/edit-attribute-form',
                [
                    'id' => $id,
                ]
            );

            die;
        }

        echo $this->renderer->render(
            'admin/entity-management/edit-group-form',
            [
                'id' => $id,
            ]
        );
        die;
    }
}
