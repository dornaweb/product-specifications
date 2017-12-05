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

$term = get_term_by('id', $attribute_id, 'spec-attr');

$tables = new WP_Query( array(
	'post_type' => 'specs-table',
	'showposts' => -1
) );
$tbl_array = $tables->get_posts(); ?>

<form action="#" method="post" id="dwps_modify_form">
	<p>
		<label for="attr_name"><?php _e('Attribute name : ', 'dwspecs'); ?></label>
		<input type="text" name="attr_name" value="<?php echo $term->name; ?>" id="attr_name" aria-required="true">
	</p>

	<p>
		<label for="attr_slug"><?php _e('Attribute slug : ', 'dwspecs'); ?></label>
		<input type="text" name="attr_slug" value="<?php echo urldecode( $term->slug ); ?>" id="attr_slug" placeholder="<?php _e('Optional', 'dwspecs'); ?>">
	</p>

	<?php if( sizeof( $tbl_array ) > 0 ) : ?>

		<p>
			<label for="attr_table"><?php _e('Table : ', 'dwspecs'); ?></label>

			<select name="attr_table" id="attr_table" aria-required="true" data-tables='<?php echo dw_get_table_groups('json'); ?>'>
				<option value=""><?php _e('Select a table', 'dwspecs'); ?></option>
				<?php foreach( $tbl_array as $table ) {
					echo '<option value="'. $table->ID .'">'. $table->post_title .'</option>';
				} ?>
			</select>
		</p>

	<?php endif; ?>

	<p>
		<label for="attr_group"><?php _e('Attribute group : ', 'dwspecs'); ?></label>
		<select name="attr_group" id="attr_group" aria-required="true">
			<option value=""><?php _e('Select a group', 'dwspecs'); ?></option>
			<?php
				$all_groups = get_terms( array(
					'taxonomy'   => 'spec-group',
					'hide_empty' => false,
				) );

				foreach( $all_groups as $group ){
					$selected = selected( $group->term_id, get_term_meta( $term->term_id, 'attr_group', true ) );
					echo "<option value=\"{$group->term_id}\" {$selected}>{$group->name}</option>";
				}
			?>
		</select>
	</p>

	<p>
		<label for="attr_type"><?php _e('Attribute field Type : ', 'dwspecs'); ?></label>
		<select name="attr_type" id="attr_type" aria-required="true">
			<option value="" <?php echo selected( false, get_term_meta( $term->term_id, 'attr_type', true ) ); ?>><?php _e('Select a field type', 'dwspecs'); ?></option>
			<option value="text" <?php echo selected( 'text', get_term_meta( $term->term_id, 'attr_type', true ) ); ?>><?php _e('Text', 'dwspecs'); ?>
			<option value="select" <?php echo selected( 'select', get_term_meta( $term->term_id, 'attr_type', true ) ); ?>><?php _e('Select', 'dwspecs'); ?>
			<option value="radio" <?php echo selected( 'radio', get_term_meta( $term->term_id, 'attr_type', true ) ); ?>><?php _e('Radio', 'dwspecs'); ?>
			<option value="textarea" <?php echo selected( 'textarea', get_term_meta( $term->term_id, 'attr_type', true ) ); ?>><?php _e('Textarea', 'dwspecs'); ?>
			<option value="true_false" <?php echo selected( 'true_false', get_term_meta( $term->term_id, 'attr_type', true ) ); ?>><?php _e('True/false', 'dwspecs'); ?>
		</select>
	</p>

	<p style="display:none;">
		<label for="attr_values"><?php _e('Values : ', 'dwspecs'); ?></label>
		<textarea name="attr_values" id="attr_values"><?php
			$attr_values = get_term_meta( $term->term_id, 'attr_values', true );
			if( is_array( $attr_values ) )
				echo implode("\n", $attr_values);
		?></textarea>
	</p>

	<p>
		<label for="attr_default"><?php _e('Default value : ', 'dwspecs'); ?></label>
		<span id="default_value_wrap"><input type="text" data-initial="true" name="attr_default" id="attr_default" value="<?php echo get_term_meta( $term->term_id, 'attr_default', true ); ?>"></span>
	</p>

	<p>
		<label for="attr_desc"><?php _e('Description : ', 'dwspecs'); ?></label>
		<textarea name="attr_desc" id="attr_desc" placeholder="<?php _e('Optional', 'dwspecs'); ?>"><?php echo $term->description; ?></textarea>
	</p>

	<input type="hidden" name="action" value="dwps_modify_attributes">
	<input type="hidden" name="do" value="edit">
	<input name="id" value="<?php echo $attribute_id; ?>" type="hidden">
	<?php wp_nonce_field( 'dwps_modify_attributes', 'dwps_modify_attributes_nonce' ); ?>
	<input type="submit" value="<?php _e('Update attribute', 'dwspecs'); ?>" class="button button-primary">
</form>