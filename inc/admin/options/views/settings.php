<?php
/**
 * Spec. table Settings template
 *
 * @author Am!n <www.dornaweb.com>
 * @package Wordpress
 * @subpackage Product Specifications Table Plugin
 * @link http://www.dornaweb.com
 * @license GNU General Public License v2 or later, http://www.gnu.org/licenses/gpl-2.0.html
 * @version 0.1
*/ ?>

<div class="dwps-page">
	<div class="dwps-settings-wrap">
		<h3><?php _e('Product specifications general settings', 'dwspecs'); ?></h3>
		<p class="title-note"></p>

		<div class="dwps-box-wrapper dwps-box-padded clearfix">
			<div class="dwps-box-top clearfix">
				<h4><?php _e('settings', 'dwspecs'); ?></h4>
			</div><!-- .dwps-box-top -->

			<form method="post" action="options.php">
				<p class="dwps-field-wrap">
					<strong class="label"><?php _e('Views per page', 'dwspecs'); ?></strong>
					<em class="note"><?php _e('How many records should be seen in a page?', 'dwspecs'); ?></em>
					<input type="number" name="dwps_view_per_page" value="<?php echo get_option('dwps_view_per_page'); ?>">
				</p>

				<p class="dwps-field-wrap">
					<strong class="label"><?php _e('Woocommerce default specs. behaviour', 'dwspecs'); ?></strong>
					<em class="note"><?php _e('Choose what to do with woocommerce default specifications table', 'dwspecs'); ?></em>

					<select name="dwps_wc_default_specs">
						<option value="remove" <?php selected( 'remove', get_option('dwps_wc_default_specs') ); ?>><?php _e('Always Remove', 'dwspecs'); ?></option>
						<option value="remove_if_specs_not_empty" <?php selected( 'remove_if_specs_not_empty', get_option('dwps_wc_default_specs') ); ?>><?php _e('Remove if product has a specs. table', 'dwspecs'); ?></option>
						<option value="keep" <?php selected( 'keep', get_option('dwps_wc_default_specs') ); ?>><?php _e('Always keep', 'dwspecs'); ?></option>
					</select>
				</p>

				<p class="dwps-field-wrap">
					<strong class="label"><?php _e('Disable plugin\'s CSS styles', 'dwspecs'); ?></strong>
					<label>
						<input type="checkbox" name="dwps_disable_default_styles" <?php checked( get_option('dwps_disable_default_styles'), 'on' ); ?>>
						<?php _e('Disable plugin\'s CSS styles', 'dwspecs'); ?>
					</label>
				</p>

				<?php settings_fields( 'dwps_options' ); ?>
				<?php submit_button(); ?>
			</form>
		</div><!-- .dwps-box-wrapper -->
	</div><!-- .dw-specs-settings-wrap -->
</div><!-- .dwps-page -->