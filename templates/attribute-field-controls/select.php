<?php

declare(strict_types=1);

use Amiut\ProductSpecs\Attribute\AttributeFieldGroup;
use Amiut\ProductSpecs\Attribute\AttributeFieldSelect;
use Amiut\ProductSpecs\Template\TemplateRenderer;

/**
 * @var AttributeFieldSelect $attribute
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

<select
    name="<?= esc_attr(sprintf("dw-attr[%d][%d]", $group->id(), $attribute->id())) ?>"
    id="<?= esc_attr($attribute->slug()) ?>"
    <?php if ($attribute->isCustomValue()) : ?>
        disabled="disabled"
    <?php endif ?>
>
    <option value=""><?= esc_html__('Select an option', 'product-specifications') ?></option>
    <?php foreach ($attribute->options() as $option) : ?>
        <option value="<?= esc_attr($option) ?>" <?php selected($option, (string) $attribute->value()) ?>>
            <?= esc_html($option) ?>
        </option>
    <?php endforeach ?>
</select>

<label class="or">
    <?= esc_html__('Or', 'product-specifications') ?>
    <input class="customvalue-switch" type="checkbox" <?php checked($attribute->isCustomValue()) ?>>
</label>

<input
    type="text"
    value="<?= $attribute->isCustomValue() ? esc_attr((string) $attribute->value()) : '' ?>"
    name="<?= esc_attr(sprintf("dw-attr[%d][%d]", $group->id(), $attribute->id())) ?>"
    class="select-custom"
    placeholder="<?= esc_attr__('Custom value', 'product-specifications') ?>"
    <?php if (!$attribute->isCustomValue()) : ?>
        disabled="disabled"
    <?php endif ?>
>
