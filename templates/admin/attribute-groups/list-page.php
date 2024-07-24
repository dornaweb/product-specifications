<?php

declare(strict_types=1);

/**
 * Spec. table Groups template
 *
 * @author Am!n <www.dornaweb.com>
 * @package WordPress
 * @subpackage Product Specifications Table Plugin
 * @link http://www.dornaweb.com
 * @license GNU General Public License v2 or later, http://www.gnu.org/licenses/gpl-2.0.html
 * @version 0.1
 */

/** Search query **/
$search_query = sanitize_text_field(filter_input(INPUT_GET, 'q', FILTER_SANITIZE_SPECIAL_CHARS));

/** records per page **/
$limit = absint(get_option('dwps_view_per_page')) ?: 15;

// just to count number of records
$all_groups = get_terms([
    'taxonomy' => 'spec-group',
    'hide_empty' => false,
    'search' => $search_query,
]);

$total_pages = sizeof($all_groups) > $limit ? ceil(sizeof($all_groups) / $limit) : 1;

$paged = absint(filter_input(INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT)) ?: 1;

$offset = ($paged - 1) * $limit;

$groups = get_terms([
    'taxonomy' => 'spec-group',
    'hide_empty' => false,
    'number' => $limit,
    'offset' => $offset,
    'search' => $search_query,
    'orderby' => 'term_id',
    'order' => 'DESC',
]) ?>

<div class="dwps-page">
    <div class="dwps-settings-wrap">
        <h3><?php echo esc_html__('Attribute Groups', 'product-specifications') ?></h3>
        <p class="title-note"></p>

        <div class="dwps-box-wrapper clearfix">
            <div class="dwps-box-top clearfix">
                <h4><?php echo esc_html__('Attribute Groups', 'product-specifications') ?></h4>
                <div class="dwps-group-searchform">
                    <form action="<?php echo esc_url(dwspecs_current_page_url()) ?>" method="get">
                        <input type="text" name="q" value="<?php echo esc_attr($search_query) ?: '' ?>" placeholder="<?php echo esc_html__('Search...', 'product-specifications') ?>">

                        <?php
                        if (!empty($_GET)) {
                            foreach ($_GET as $key => $val) {
                                $key = esc_attr($key);
                                $val = esc_attr($val);
                                if ($key !== 'q') {
                                    echo "<input type=\"hidden\" name=\"$key\" value=\"$val\">";
                                }
                            }
                        } ?>
                        <button type="submit"><i class="dashicons dashicons-search"></i></button>
                    </form>
                </div><!-- .dwps-group-searchform -->
            </div><!-- .dwps-box-top -->

            <div id="dwps_table_wrap">
                <table class="dwps-table" id="dwps_table">
                    <thead>
                    <tr>
                        <th class="check-column"><input type="checkbox" id="cb-select-all-1" class="selectall"></th>
                        <th><?php echo esc_html__('ID', 'product-specifications') ?></th>
                        <th><?php echo esc_html__('Group name', 'product-specifications') ?></th>
                        <th><?php echo esc_html__('Group slug', 'product-specifications') ?></th>
                        <th><?php echo esc_html__('# of attributes', 'product-specifications') ?></th>
                        <th><?php echo esc_html__('Actions', 'product-specifications') ?></th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php
                    if (sizeof($groups) > 0) :
                        foreach ($groups as $group) :
                            $attributes = dwspecs_get_attributes_by_group($group->term_id) ?>
                            <tr>
                                <td class="check-column"><input class="dlt-bulk-group" type="checkbox" name="slct_group[]" value="<?php echo esc_attr($group->term_id) ?>"></td>
                                <td><?php echo esc_html($group->term_id) ?></td>
                                <td><h4><a href="#" class="edit" aria-label="<?php echo esc_attr__('Edit group', 'product-specifications') ?>" data-dwpsmodal="true" data-type="group" data-action="edit" data-id="<?php echo esc_attr($group->term_id) ?>"><?php echo esc_html($group->name) ?></a></h4></td>
                                <td><?php echo esc_html($group->slug) ?></td>
                                <td><?php echo esc_html(sizeof($attributes)) ?></td>
                                <td class="dwps-actions">
                                    <a href="#" class="delete" data-type="group" data-id="<?php echo esc_attr($group->term_id) ?>"><i class="dashicons dashicons-no"></i></a>
                                    <a href="#" role="button" class="edit" aria-label="<?php echo esc_attr__('Edit group', 'product-specifications') ?>" data-dwpsmodal="true" data-type="group" data-action="edit" data-id="<?php echo esc_attr($group->term_id) ?>"><i class="dashicons dashicons-welcome-write-blog"></i></a>

                                    <?php
                                    echo '<a class="view" href="' . esc_url(add_query_arg([ 'group_id' => $group->term_id, 'page' => 'dw-specs-attrs' ], esc_url(admin_url('admin.php')))) . '"><i class="dashicons dashicons-visibility"></i></a>' ?>

                                    <a href="#" class="re-arange" data-id="<?php echo esc_attr($group->term_id) ?>"><i class="dashicons dashicons-sort"></i></a>
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
            <!-- #groups_table_wrap -->

            <div class="dwps-buttons clearfix">
                <a id="dpws_bulk_delete_btn" class="dwps-button red-btn delete-selecteds-btn" href="#" role="button" aria-label="<?php echo esc_attr__('delete Selected attributes', 'product-specifications') ?>" disabled><?php echo esc_html__('Delete Selected', 'product-specifications') ?></a>

                <a data-dwpsmodal="true" id="dpws_add_new_btn" class="dwps-button add-new-btn" href="#" role="button" aria-label="<?php echo esc_attr__('Add a new group', 'product-specifications') ?>"><?php echo esc_html__('Add new', 'product-specifications') ?></a>

                <div class="dwps-pagination">
                    <?php echo paginate_links([
                        'base' => '%_%',
                        'format' => '?paged=%#%',
                        'total' => $total_pages,
                        'current' => isset($_GET['paged']) ? absint($_GET['paged']) : 0,
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
            "type" : "group"
        },
        "modal" : {
            "title" : "<?php echo esc_html__("Delete Attribute", "dwspecs") ?>",
            "content" : "<?php echo esc_html__("Are you sure you want to delete selected attribute(s)?", "dwspecs") ?>",
            "buttons" : {
                "primary": {
                    "value": "<?php echo esc_html__("Yes", "dwspecs") ?>",
                    "href": "#",
                    "className": "modal-btn btn-confirm btn-warn"
                },
                "secondary": {
                    "value": "<?php echo esc_html__("No", "dwspecs") ?>",
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
        "add_btn"     : "<?php echo esc_attr__('Add', 'product-specifications') ?>",
        "edit_btn"    : "<?php echo esc_attr__('Update', 'product-specifications') ?>",
        "add_title"   : "<?php echo esc_attr__('Add new group', 'product-specifications') ?>",
        "edit_title"  : "<?php echo esc_attr__('Edit group', 'product-specifications') ?>",
        "re_arrange"  : "<?php echo esc_attr__('Re arrange attributes', 'product-specifications') ?>"
    }
</script>

<script id="modify_form_template" type="x-tmpl-mustache">
    <form action="#" method="post" id="dwps_modify_form">
        <p>
            <label for="group_name"><?php echo esc_html__('Group name : ', 'product-specifications') ?></label>
            <input type="text" name="group_name" value="" id="group_name" aria-required="true">
        </p>

        <p>
            <label for="group_slug"><?php echo esc_html__('Group slug : ', 'product-specifications') ?></label>
            <input type="text" name="group_slug" value="" id="group_slug" placeholder="<?php echo esc_attr__('Optional', 'product-specifications') ?>">
        </p>

        <p>
            <label for="group_desc"><?php echo esc_html__('Description : ', 'product-specifications') ?></label>
            <textarea name="group_desc" id="group_desc" placeholder="<?php echo esc_attr__('Optional', 'product-specifications') ?>"></textarea>
        </p>

        <input type="hidden" name="action" value="dwps_modify_groups">
        <input type="hidden" name="do" value="add">
    <?php wp_nonce_field('dwps_modify_groups', 'dwps_modify_groups_nonce') ?>
    <input type="submit" value="<?php echo esc_attr__('Add Group', 'product-specifications') ?>" class="button button-primary">
    </form>
</script>
