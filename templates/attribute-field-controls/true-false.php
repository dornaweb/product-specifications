<?php

declare(strict_types=1);

use Amiut\ProductSpecs\Attribute\AttributeFieldGroup;
use Amiut\ProductSpecs\Attribute\AttributeFieldTrueFalse;
use Amiut\ProductSpecs\Template\TemplateRenderer;

defined('ABSPATH') || exit;

/**
 * @var AttributeFieldTrueFalse $attribute
 * @var AttributeFieldGroup $group
 * @var TemplateRenderer $renderer
 * @var array $data
 */

[
    'attribute' => $attribute,
    'group' => $group,
    'renderer' => $renderer,
] = $data;

$value = $attribute->value() ?? $attribute->default();

?>

<label>
    <?= esc_html($attribute->name()) ?> :
</label>

<label>
    <?= esc_html__('Yes', 'product-specifications') ?>
    <input type="radio"
        name="<?= esc_attr(sprintf("dw-attr[%d][%d]", $group->id(), $attribute->id())) ?>"
        value="yes"
        <?php checked($value) ?>
    >
</label>

<label>
    <?= esc_html__('No', 'product-specifications') ?>
    <input type="radio"
        name="<?= esc_attr(sprintf("dw-attr[%d][%d]", $group->id(), $attribute->id())) ?>"
        value="no"
        <?php checked(!$value) ?>
    >
</label>
