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

final class GroupReArrangeFormUi
{
    private TemplateRenderer $renderer;

    public function __construct(TemplateRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function render(): void
    {
        $id = (int) filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if ($id < 1) {
            return;
        }

        echo $this->renderer->render(
            'admin/entity-management/group-rearrange-form',
            [
                'id' => $id,
            ]
        );
        die;
    }
}
