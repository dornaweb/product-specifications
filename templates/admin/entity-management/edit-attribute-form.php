<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

/**
 * @var int $attributeId
 * @var array $data
 */
[
        'id' => $attributeId,
] = $data;

/**
 * @var WP_Term $term
 */
$term = get_term_by('id', $attributeId, 'spec-attr');
?>

<form action="#" method="post" id="dwps_modify_form">
    <p>
        <label for="attr_name"><?= esc_html__('Attribute name : ', 'product-specifications') ?></label>
        <input type="text" name="attr_name" value="<?= esc_attr($term->name) ?>" id="attr_name" aria-required="true">
    </p>

    <p>
        <label for="attr_slug"><?= esc_html__('Attribute slug : ', 'product-specifications') ?></label>
        <input type="text" name="attr_slug" value="<?= esc_attr(urldecode($term->slug)) ?>" id="attr_slug" placeholder="<?= esc_attr__('Optional', 'product-specifications') ?>">
    </p>

    <p>
        <label for="attr_group"><?= esc_html__('Attribute group : ', 'product-specifications') ?></label>
        <select name="attr_group" id="attr_group" aria-required="true">
            <option value=""><?= esc_html__('Select a group', 'product-specifications') ?></option>
            <?php
                $all_groups = get_terms([
                    'taxonomy' => 'spec-group',
                    'hide_empty' => false,
                ]);

                foreach ($all_groups as $group) :
                    $group_id = esc_attr($group->term_id);
                    $group_name = esc_attr($group->name);
                    ?>
                    <option
                        value="<?= esc_attr($group_id) ?>"
                        <?php selected($group->term_id, get_term_meta($term->term_id, 'attr_group', true)) ?>>
                        <?= esc_html($group_name) ?>
                    </option>";
                <?php endforeach ?>
        </select>
    </p>

    <p>
        <label for="attr_type"><?= esc_html__('Attribute field Type : ', 'product-specifications') ?></label>
        <select name="attr_type" id="attr_type" aria-required="true">
            <option value="" <?php selected(false, get_term_meta($term->term_id, 'attr_type', true)) ?>><?= esc_html__('Select a field type', 'product-specifications') ?></option>
            <option value="text" <?php selected('text', get_term_meta($term->term_id, 'attr_type', true)) ?>><?= esc_html__('Text', 'product-specifications') ?>
            <option value="select" <?php selected('select', get_term_meta($term->term_id, 'attr_type', true)) ?>><?= esc_html__('Select', 'product-specifications') ?>
            <option value="radio" <?php selected('radio', get_term_meta($term->term_id, 'attr_type', true)) ?>><?= esc_html__('Radio', 'product-specifications') ?>
            <option value="textarea" <?php selected('textarea', get_term_meta($term->term_id, 'attr_type', true)) ?>><?= esc_html__('Textarea', 'product-specifications') ?>
            <option value="true_false" <?php selected('true_false', get_term_meta($term->term_id, 'attr_type', true)) ?>><?= esc_html__('True/false', 'product-specifications') ?>
        </select>
    </p>

    <p style="display:none;">
        <label for="attr_values"><?= esc_html__('Values : ', 'product-specifications') ?></label>
        <textarea name="attr_values" id="attr_values"><?php
            $attr_values = get_term_meta($term->term_id, 'attr_values', true);
        if (is_array($attr_values)) {
            echo esc_html(implode("\n", $attr_values));
        }
        ?></textarea>
    </p>

    <p>
        <label for="attr_default"><?= esc_html__('Default value : ', 'product-specifications') ?></label>
        <span id="default_value_wrap"><input type="text" data-initial="true" name="attr_default" id="attr_default" value="<?= esc_attr(get_term_meta($term->term_id, 'attr_default', true)) ?>"></span>
    </p>

    <p>
        <label for="attr_desc"><?= esc_html__('Description : ', 'product-specifications') ?></label>
        <textarea name="attr_desc" id="attr_desc" placeholder="<?= esc_attr__('Optional', 'product-specifications') ?>"><?= esc_html($term->description) ?></textarea>
    </p>

    <input type="hidden" name="action" value="dwps_modify_attributes">
    <input type="hidden" name="do" value="edit">
    <input name="id" value="<?= esc_attr((string) $attributeId) ?>" type="hidden">
    <?php wp_nonce_field('dwps_modify_attributes', 'dwps_modify_attributes_nonce') ?>
    <input type="submit" value="<?= esc_attr__('Update attribute', 'product-specifications') ?>" class="button button-primary">
</form>
