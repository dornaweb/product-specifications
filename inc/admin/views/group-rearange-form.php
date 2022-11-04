<?php
/**
 * Group Re-arrange attributes form
 *
 * @author Am!n <www.dornaweb.com>
 * @package Wordpress
 * @subpackage Product Specifications Table Plugin
 * @link http://www.dornaweb.com
 * @license GNU General Public License v2 or later, http://www.gnu.org/licenses/gpl-2.0.html
 * @version 0.1
*/

$term = get_term_by('id', $group_id, 'spec-group');
$attributes = get_term_meta( $group_id, 'attributes', true ) ?: array();
?>

<form action="#" method="post" id="dwps_modify_form">
	<ul class="re-arrange-attributes dpws-sortable">
		<?php
		foreach( $attributes as $attr ) {
			if( term_exists( intval( $attr ), 'spec-attr' ) ){
				$attr = get_term_by('id', $attr, 'spec-attr');?>
				<li>
					<input type="checkbox" readonly checked value="<?php echo $attr->term_id; ?>" name="attr[]">
					<?php echo $attr->name; ?>
				</li>
		<?php
			}
		}

		if( sizeof( $attributes ) == 0 ) _e('This group has no attributes', 'product-specifications'); ?>
	</ul>

	<input name="action" value="dwps_group_rearange" type="hidden">
	<input name="group_id" value="<?php echo $group_id; ?>" type="hidden">

	<?php wp_nonce_field( 'dwps_group_rearange', 'dwps_group_rearange_nonce' ); ?>
	<input value="<?php _e('Update Arrangement', 'product-specifications'); ?>" class="button button-primary" type="submit" style="float:left;">
	<div class="clearfix"></div>
</form>