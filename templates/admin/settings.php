<?php

declare(strict_types=1);

?>

<div class="dwps-page">
    <div class="dwps-settings-wrap">
        <h3><?php echo esc_html__('Product specifications general settings', 'product-specifications') ?></h3>
        <p class="title-note"></p>

        <div class="dwps-box-wrapper dwps-box-padded clearfix">
            <div class="dwps-box-top clearfix">
                <h4><?php echo esc_html__('settings', 'product-specifications') ?></h4>
            </div><!-- .dwps-box-top -->

            <form method="post" action="options.php">
                <p class="dwps-field-wrap">
                    <strong class="label"><?php echo esc_html__('Specs Tab Title', 'product-specifications') ?></strong>
                    <em class="note"><?php echo esc_html__('The title of product specs. tab in product single page', 'product-specifications') ?></em>
                    <input type="text" name="dwps_tab_title" value="<?php echo esc_attr(get_option('dwps_tab_title')) ?>">
                </p>

                <p class="dwps-field-wrap">
                    <strong class="label"><?php echo esc_html__('Views per page', 'product-specifications') ?></strong>
                    <em class="note"><?php echo esc_html__('How many records should be seen in a page?', 'product-specifications') ?></em>
                    <input type="number" name="dwps_view_per_page" value="<?php echo esc_attr(get_option('dwps_view_per_page')) ?>">
                </p>

                <p class="dwps-field-wrap">
                    <strong class="label"><?php echo esc_html__('Woocommerce default specs. behaviour', 'product-specifications') ?></strong>
                    <em class="note"><?php echo esc_html__('Choose what to do with woocommerce default specifications table', 'product-specifications') ?></em>

                    <select name="dwps_wc_default_specs">
                        <option value="remove" <?php selected('remove', get_option('dwps_wc_default_specs')) ?>><?php echo esc_html__('Always Remove', 'product-specifications') ?></option>
                        <option value="remove_if_specs_not_empty" <?php selected('remove_if_specs_not_empty', get_option('dwps_wc_default_specs')) ?>><?php echo esc_html__('Remove if product has a specs. table', 'product-specifications') ?></option>
                        <option value="keep" <?php selected('keep', get_option('dwps_wc_default_specs')) ?>><?php echo esc_html__('Always keep', 'product-specifications') ?></option>
                    </select>
                </p>

                <p class="dwps-field-wrap">
                    <strong class="label"><?php echo esc_html__('Disable plugin\'s CSS styles', 'product-specifications') ?></strong>
                    <label>
                        <input type="checkbox" name="dwps_disable_default_styles" <?php checked(get_option('dwps_disable_default_styles'), 'on') ?>>
                        <?php echo esc_html__('Disable plugin\'s CSS styles', 'product-specifications') ?>
                    </label>
                </p>

                <?php settings_fields('dwps_options') ?>
                <?php submit_button() ?>
            </form>
        </div><!-- .dwps-box-wrapper -->
    </div><!-- .dw-specs-settings-wrap -->
</div><!-- .dwps-page -->
