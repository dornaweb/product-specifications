<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

/**
 * @var \Amiut\ProductSpecs\Attribute\AttributeField $attribute
 * @var \Amiut\ProductSpecs\Attribute\AttributeFieldGroup $group
 * @var \Amiut\ProductSpecs\Template\TemplateRenderer $renderer
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

<textarea
    name="<?= esc_attr(sprintf("dw-attr[%d][%d]", (string) $group->id(), $attribute->id())) ?>"
    id="<?= esc_attr($attribute->slug()) ?>"><?= esc_html((string) ($attribute->value() ?? $attribute->default())) ?></textarea>
