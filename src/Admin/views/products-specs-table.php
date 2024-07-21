<?php

declare(strict_types=1);

/**
 * Product Spec. table metabox template
 *
 * @author Am!n <www.dornaweb.com>
 * @package WordPress
 * @subpackage Product Specifications Table Plugin
 * @link http://www.dornaweb.com
 * @license GNU General Public License v2 or later, http://www.gnu.org/licenses/gpl-2.0.html
 * @version 0.1
*/

$table_id = get_post_meta($post->ID, '_dwps_table', true); ?>

<div class="dwsp-meta-wrap dwps-page">
    <strong class="title"><?php echo esc_html__('Select a table : ', 'product-specifications'); ?></strong>

    <div class="dwsp-meta-item">
        <select name="specs_table" id="spec_tables_list" data-postid="<?php echo esc_html($post->ID); ?>">
            <option value="0"><?php echo esc_html__('none', 'product-specifications'); ?></option>
            <?php
            foreach ($tables as $table) {
                echo '<option value="' . esc_attr($table->ID) . '"' . selected($table_id, $table->ID) . '>' . esc_html($table->post_title) . '</option>';
            } ?>
        </select>
    </div><!-- .dwsp-meta-item -->

    <div class="dwps-spec-table-wrap tab-boxes clearfix" id="specifications_table_wrapper">
        <?php
        if ($table_id && $table_id !== '0') {
            \Amiut\ProductSpecs\Admin\Admin::get_template('views/product-load-table', [ 'table_id' => $table_id, 'post' => $post ]);
        } ?>
    </div><!-- #specifications_table_wrapper -->

    <?php wp_nonce_field('dw-specs-table-metas', 'dwps_metabox_nonce'); ?>

</div><!-- .dw-specs-meta-wrap -->

<script id="loading_svg" type="x-tmpl-mustache">
    <svg width="120" height="30" viewBox="0 0 120 30" xmlns="http://www.w3.org/2000/svg" fill="#fff">
        <circle cx="15" cy="15" r="15">
            <animate attributeName="r" from="15" to="15"
                    begin="0s" dur="0.8s"
                    values="15;9;15" calcMode="linear"
                    repeatCount="indefinite" />
            <animate attributeName="fill-opacity" from="1" to="1"
                    begin="0s" dur="0.8s"
                    values="1;.5;1" calcMode="linear"
                    repeatCount="indefinite" />
        </circle>
        <circle cx="60" cy="15" r="9" fill-opacity="0.3">
            <animate attributeName="r" from="9" to="9"
                    begin="0s" dur="0.8s"
                    values="9;15;9" calcMode="linear"
                    repeatCount="indefinite" />
            <animate attributeName="fill-opacity" from="0.5" to="0.5"
                    begin="0s" dur="0.8s"
                    values=".5;1;.5" calcMode="linear"
                    repeatCount="indefinite" />
        </circle>
        <circle cx="105" cy="15" r="15">
            <animate attributeName="r" from="15" to="15"
                    begin="0s" dur="0.8s"
                    values="15;9;15" calcMode="linear"
                    repeatCount="indefinite" />
            <animate attributeName="fill-opacity" from="1" to="1"
                    begin="0s" dur="0.8s"
                    values="1;.5;1" calcMode="linear"
                    repeatCount="indefinite" />
        </circle>
    </svg>
</script>
