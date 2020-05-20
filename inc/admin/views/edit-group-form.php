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
		<label for="group_name"><?php _e('Group name : ', 'dwspecs'); ?></label>
		<input name="group_name" value="<?php echo $term->name; ?>" id="group_name" aria-required="true" type="text">
	</p>

	<p>
		<label for="group_slug"><?php _e('Group slug : ', 'dwspecs'); ?></label>
		<input name="group_slug" value="<?php echo urldecode( $term->slug ); ?>" id="group_slug" placeholder="<?php _e('Optional', 'dwspecs'); ?>" type="text">
	</p>

	<p>
		<label for="group_desc"><?php _e('Description : ', 'dwspecs'); ?></label>
		<textarea name="group_desc" id="group_desc" placeholder="<?php _e('Optional', 'dwspecs'); ?>"><?php echo $term->description; ?></textarea>
	</p>

	<input name="action" value="dwps_modify_groups" type="hidden">
	<input name="do" value="edit" type="hidden">
	<input name="group_id" value="<?php echo $group_id; ?>" type="hidden">

	<?php wp_nonce_field( 'dwps_modify_groups', 'dwps_modify_groups_nonce' ); ?>
	<input value="<?php _e('Update Group', 'dwspecs'); ?>" class="button button-primary" type="submit">
</form>