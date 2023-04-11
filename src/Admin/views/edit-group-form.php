<?php
/**
 * Group edit form
 *
 * @author Am!n <www.dornaweb.com>
 * @package Wordpress
 * @subpackage Product Specifications Table Plugin
 * @link http://www.dornaweb.com
 * @license GNU General Public License v2 or later, http://www.gnu.org/licenses/gpl-2.0.html
 * @version 0.1
*/

$term = get_term_by('id', $group_id, 'spec-group'); ?>

<form action="#" method="post" id="dwps_modify_form">
	<p>
		<label for="group_name"><?php echo esc_html__('Group name : ', 'product-specifications'); ?></label>
		<input name="group_name" value="<?php echo esc_attr($term->name); ?>" id="group_name" aria-required="true" type="text">
	</p>

	<p>
		<label for="group_slug"><?php echo esc_html__('Group slug : ', 'product-specifications'); ?></label>
		<input name="group_slug" value="<?php echo esc_attr( $term->slug ); ?>" id="group_slug" placeholder="<?php echo esc_attr__('Optional', 'product-specifications'); ?>" type="text">
	</p>

	<p>
		<label for="group_desc"><?php echo esc_html__('Description : ', 'product-specifications'); ?></label>
		<textarea name="group_desc" id="group_desc" placeholder="<?php echo esc_attr__('Optional', 'product-specifications'); ?>"><?php echo esc_html($term->description); ?></textarea>
	</p>

	<input name="action" value="dwps_modify_groups" type="hidden">
	<input name="do" value="edit" type="hidden">
	<input name="group_id" value="<?php echo esc_attr($group_id); ?>" type="hidden">

	<?php wp_nonce_field( 'dwps_modify_groups', 'dwps_modify_groups_nonce' ); ?>
	<input value="<?php echo esc_attr__('Update Group', 'product-specifications'); ?>" class="button button-primary" type="submit">
</form>
