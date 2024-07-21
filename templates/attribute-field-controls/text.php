<?php

declare(strict_types=1);

use Amiut\ProductSpecs\Attribute\AttributeFieldGroup;
use Amiut\ProductSpecs\Attribute\AttributeFieldText;
use Amiut\ProductSpecs\Template\TemplateRenderer;

/**
 * @var AttributeFieldText $attribute
 * @var AttributeFieldGroup $group
 * @var TemplateRenderer $renderer
 * @var array $data
 */

[
    'attribute' => $attribute,
    'group' => $group,
    'renderer' => $renderer,
] = $data;
?>

<label for="<?= esc_attr($attribute->slug()) ?>">
    <?= esc_html($attribute->name()) ?> :
</label>

<input
    type="text"
    name="<?= esc_attr(sprintf("dw-attr[%s][%s]", $group->id(), $attribute->id())) ?>"
    id="<?= esc_attr($attribute->slug()) ?>"
    value="<?= esc_attr((string) ($attribute->value() ?? $attribute->default())) ?>"
>
