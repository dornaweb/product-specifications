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

				<?php settings_fields( 'dwps_options' ); ?>
				<?php submit_button(); ?>
			</form>
		</div><!-- .dwps-box-wrapper -->
	</div><!-- .dw-specs-settings-wrap -->
</div><!-- .dwps-page -->