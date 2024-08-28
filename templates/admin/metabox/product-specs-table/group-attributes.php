<?php

declare(strict_types=1);

use Amiut\ProductSpecs\Attribute\AttributeFieldGroup;
use Amiut\ProductSpecs\Template\TemplateRenderer;

defined('ABSPATH') || exit;

/**
 * @var AttributeFieldGroup $group
 * @var TemplateRenderer $renderer
 * @var array $data
 */

[
    'group' => $group,
    'renderer' => $renderer,
] = $data;

?>

<?php foreach ($group->attributes() as $attribute) : ?>
    <li class="dw-table-field-wrap">
        <?= $renderer->render( // phpcs:ignore
            $attribute->templatePath(),
            ['attribute' => $attribute, 'group' => $group] // phpcs:ignore
        ) ?>
    </li>
<?php endforeach ?>
