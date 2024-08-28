<?php

declare(strict_types=1);

use Amiut\ProductSpecs\Repository\EntityCollection;

defined('ABSPATH') || exit;

/**
 * @var EntityCollection $collection
 * @var int $groupId
 * @var string $searchKeyword
 * @var array $data
 */
[
    'collection' => $collection,
    'searchKeyword' => $searchKeyword,
    'groupId' => $groupId,
] = $data;
?>

<div class="dwps-page">
    <div class="dwps-settings-wrap">
        <h3><?= esc_html__('Attributes', 'product-specifications') ?></h3>
        <p class="title-note"></p>

        <div class="dwps-box-wrapper clearfix">
            <div class="dwps-box-top clearfix">
                <h4><?= esc_html__('Attributes', 'product-specifications') ?></h4>
                <div class="dwps-group-searchform">
                    <form action="" method="get">
                        <input
                            type="text"
                            name="q"
                            value="<?= esc_attr($searchKeyword) ?: '' ?>"
                            placeholder="<?= esc_attr__('Search...', 'product-specifications') ?>"
                        />

                        <input type="hidden" name="page" value="dw-specs-attrs" />

                        <?php if ($groupId > 0) : ?>
                            <input name="group_id" type="hidden" value="<?= esc_attr($groupId) ?>" />
                        <?php endif ?>

                        <button type="submit"><i class="dashicons dashicons-search"></i></button>
                    </form>
                </div><!-- .dwps-group-searchform -->
            </div><!-- .dwps-box-top -->

            <div id="dwps_table_wrap">
                <table class="dwps-table" id="dwps_table">
                    <thead>
                    <tr>
                        <th class="check-column"><input type="checkbox" id="cb-select-all-1" class="selectall"></th>
                        <th><?= esc_html__('ID', 'product-specifications') ?></th>
                        <th><?= esc_html__('Attribute name', 'product-specifications') ?></th>
                        <th><?= esc_html__('Group name', 'product-specifications') ?></th>
                        <th><?= esc_html__('Actions', 'product-specifications') ?></th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php
                    if (count($collection) > 0) :
                        foreach ($collection as $attribute) : ?>
                            <tr>
                                <td class="check-column">
                                    <input
                                        class="dlt-bulk-group"
                                        type="checkbox"
                                        name="slct_group[]"
                                        value="<?= esc_attr($attribute->term_id) ?>"
                                    />
                                </td>

                                <td><?= esc_html($attribute->term_id) ?></td>

                                <td>
                                    <h4>
                                        <a
                                            href="#"
                                            data-type="attribute"
                                            data-dwpsmodal="true"
                                            class="edit"
                                            data-id="<?= esc_attr($attribute->term_id) ?>"
                                        >
                                            <?= esc_html($attribute->name) ?>
                                        </a>
                                    </h4>
                                </td>

                                <td>
                                    <h4>
                                        <?php
                                        $group = get_term(
                                            get_term_meta($attribute->term_id, 'attr_group', true),
                                            'spec-group'
                                        );
                                        if (!is_wp_error($group)) : ?>
                                            <a
                                                href="<?= esc_url(add_query_arg('group_id', $group->term_id, remove_query_arg('paged'))) ?>"
                                            >
                                                <?= esc_html($group->name) ?>
                                            </a>
                                        <?php endif ?>
                                    </h4>
                                </td>

                                <td class="dwps-actions">
                                    <a
                                        href="#"
                                        class="delete"
                                        data-type="attribute"
                                        data-id="<?= esc_attr($attribute->term_id) ?>"
                                    >
                                        <i class="dashicons dashicons-no"></i>
                                    </a>
                                    <a
                                        href="#"
                                        role="button"
                                        class="edit"
                                        data-dwpsmodal="true"
                                        aria-label="<?= esc_attr__('Edit group', 'product-specifications') ?>"
                                        data-type="attribute"
                                        data-id="<?= esc_attr($attribute->term_id) ?>"
                                    >
                                        <i class="dashicons dashicons-welcome-write-blog"></i>
                                    </a>
                                </td>

                            </tr>
                            <?php
                        endforeach;
                    else :
                        echo '<tr><td class="not-found" colspan="5">' . esc_html__('Nothing found', 'product-specifications') . '</td></tr>';
                    endif ?>
                    </tbody>
                </table>
            </div>
            <!-- #attributes_table_wrap -->

            <div class="dwps-buttons clearfix">
                <a id="dpws_bulk_delete_btn" class="dwps-button red-btn delete-selecteds-btn" href="#" role="button" aria-label="<?= esc_attr__('delete Selected attributes', 'product-specifications') ?>" disabled><?= esc_html__('Delete Selected', 'product-specifications') ?></a>

                <a data-dwpsmodal="true" id="dpws_add_new_btn" class="dwps-button add-new-btn" href="#" role="button" aria-label="<?= esc_attr__('Add a new attribute', 'product-specifications') ?>"><?= esc_html__('Add new', 'product-specifications') ?></a>

                <div class="dwps-pagination">
                    <?= paginate_links([
                        'base' => '%_%',
                        'format' => '?paged=%#%',
                        'total' => $collection->totalPages(),
                        'current' => $collection->currentPage(),
                        'show_all' => false,
                        'end_size' => 4,
                        'mid_size' => 2,
                        'prev_next' => true,
                        'prev_text' => esc_html__('«', 'product-specifications'),
                        'next_text' => esc_html__('»', 'product-specifications'),
                        'type' => 'plain',
                        'add_args' => false,
                        'add_fragment' => '',
                        'before_page_number' => '',
                        'after_page_number' => '',
                    ]) ?>
                </div><!-- .pagination -->
            </div><!-- .dwps-buttons -->

        </div><!-- .dw-box-wrapper -->
    </div><!-- .dw-specs-settings-wrap -->
</div><!-- .dwps-page -->

<script id="dwps_delete_template" type="x-tmpl-mustache" data-templateType="JSON">
    {
        "data" : {
            "type" : "attribute"
        },
        "modal" : {
            "title" : "<?= esc_attr__("Delete Attribute", "product-specifications") ?>",
            "content" : "<?= esc_attr__("Are you sure you want to delete selected attribute(s)?", "product-specifications") ?>",
            "buttons" : {
                "primary": {
                    "value": "<?= esc_attr__("Yes", "product-specifications") ?>",
                    "href": "#",
                    "className": "modal-btn btn-confirm btn-warn"
                },
                "secondary": {
                    "value": "<?= esc_attr__("No", "product-specifications") ?>",
                    "className": "modal-btn btn-cancel",
                    "href": "#",
                    "closeOnClick": true
                }
            }
        }
    }
</script>

<script id="dwps_texts_template" type="x-tmpl-mustache">
    {
        "add_btn"     : "<?= esc_attr__('Add', 'product-specifications') ?>",
        "edit_btn"    : "<?= esc_attr__('Update', 'product-specifications') ?>",
        "add_title"   : "<?= esc_attr__('Add new attribute', 'product-specifications') ?>",
        "edit_title"  : "<?= esc_attr__('Edit attribute', 'product-specifications') ?>"
    }
</script>

<script id="modify_form_template" type="x-tmpl-mustache">
    <form action="#" method="post" id="dwps_modify_form">
        <p>
            <label for="attr_name"><?= esc_html__('Attribute name : ', 'product-specifications') ?></label>
            <input type="text" name="attr_name" value="" id="attr_name" aria-required="true">
        </p>

        <p>
            <label for="attr_slug"><?= esc_html__('Attribute slug : ', 'product-specifications') ?></label>
            <input type="text" name="attr_slug" value="" id="attr_slug" placeholder="<?= esc_attr__('Optional', 'product-specifications') ?>">
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
        $group_name = esc_html($group->name) ?>
            <option value="<?= esc_attr($group->term_id) ?>">
                <?= esc_html($group_name) ?>
            </option>
    <?php endforeach ?>
    </select>
</p>

<p>
    <label for="attr_type"><?= esc_html__('Attribute field Type : ', 'product-specifications') ?></label>
            <select name="attr_type" id="attr_type" aria-required="true">
                <option value=""><?= esc_html__('Select a field type', 'product-specifications') ?></option>
                <option value="text"><?= esc_html__('Text', 'product-specifications') ?>
    <option value="select"><?= esc_html__('Select', 'product-specifications') ?>
    <option value="radio"><?= esc_html__('Radio', 'product-specifications') ?>
    <option value="textarea"><?= esc_html__('Textarea', 'product-specifications') ?>
    <option value="true_false"><?= esc_html__('True/false', 'product-specifications') ?>
    </select>
</p>

<p style="display:none;">
    <label for="attr_values"><?= esc_html__('Values : ', 'product-specifications') ?></label>
            <textarea name="attr_values" id="attr_values"></textarea>
        </p>

        <p>
            <label for="attr_default"><?= esc_html__('Default value : ', 'product-specifications') ?></label>
            <span id="default_value_wrap"><input type="text" name="attr_default" id="attr_default" value=""></span>
        </p>

        <p>
            <label for="attr_desc"><?= esc_html__('Description : ', 'product-specifications') ?></label>
            <textarea name="attr_desc" id="attr_desc" placeholder="<?= esc_attr__('Optional', 'product-specifications') ?>"></textarea>
        </p>

        <input type="hidden" name="action" value="dwps_modify_attributes">
        <input type="hidden" name="do" value="add">
    <?php wp_nonce_field('dwps_modify_attributes', 'dwps_modify_attributes_nonce') ?>
    <input type="submit" value="<?= esc_attr__('Add attribute', 'product-specifications') ?>" class="button button-primary">
    </form>
</script>
