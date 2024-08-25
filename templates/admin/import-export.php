<?php

declare(strict_types=1);

?>

<div class="dwps-tools-wrap">
    <h3><?php echo esc_html__('Import / Export tables and fields', 'product-specifications') ?></h3>
    <p class="title-note"></p>

    <div class="dwps-box-wrapper dwps-tools-box clearfix">
        <h3><?php echo esc_html__('Export specification tables', 'product-specifications') ?></h3>
        <p><?php echo esc_html__('You can choose the tables you want to export and hit the download button , a "JSON" file will be downloaded , you can impot that later or on another WordPress installation.', 'product-specifications') ?></p>
        <div class="dwps-export-import-wrap">
            <div class="dwps-export-box">
                <h2><?php echo esc_html__('Export Product Specifications data', 'product-specifications') ?></h2>
                <p><?php echo esc_html__('You can choose to export only tables data or also with product data', 'product-specifications') ?></p>
                <form action="<?= esc_url(admin_url('admin-ajax.php')) ?>" method="post">
                    <p><label><input type="checkbox" name="include_products"> <?php echo esc_html__('Include Products data (file size may increase)', 'product-specifications') ?></label></p>

                    <?php wp_nonce_field('dwspecs_nonce_export', 'dws_ex_nonce') ?>
                    <input type="hidden" name="action" value="dwspecs_export_data">
                    <button class="button primary" type="submit"><?php echo esc_html__('Download', 'product-specifications') ?></button>
                </form>
            </div><!-- .dwps-export-box -->

            <div class="dwps-import-box">
                <h2><?php echo esc_html__('Import Product Specifications data', 'product-specifications') ?></h2>
                <p><?php echo esc_html__('Import your "JSON" file here', 'product-specifications') ?></p>
                <form id="dwps_import_data_form" action="<?php echo esc_url(admin_url('admin-ajax.php')) ?>" method="post">
                    <p><label><?php echo esc_html__('Select JSON file', 'product-specifications') ?></label><br><input type="file" name="file"></p>

                    <?php wp_nonce_field('dwspecs_nonce_import', 'dws_im_nonce') ?>
                    <input type="hidden" name="action" value="dwspecs_import_data">
                    <button class="button primary" type="submit"><?php echo esc_html__('Import', 'product-specifications') ?></button>
                </form>

                <div id="dwspecs_import_results"></div>
            </div><!-- .dwps-import-box -->
        </div>
    </div>
</div><!-- .dw-specs-tools-wrap -->
