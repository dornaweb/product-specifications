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
		<label for="attr_name"><?php echo esc_html__('Attribute name : ', 'product-specifications'); ?></label>
		<input type="text" name="attr_name" value="<?php echo esc_attr($term->name); ?>" id="attr_name" aria-required="true">
	</p>

	<p>
		<label for="attr_slug"><?php echo esc_html__('Attribute slug : ', 'product-specifications'); ?></label>
		<input type="text" name="attr_slug" value="<?php echo esc_attr(urldecode( $term->slug )); ?>" id="attr_slug" placeholder="<?php echo esc_attr__('Optional', 'product-specifications'); ?>">
	</p>

	<?php if( sizeof( $tbl_array ) > 0 ) : ?>

		<p>
			<label for="attr_table"><?php echo esc_html__('Table : ', 'product-specifications'); ?></label>

			<select name="attr_table" id="attr_table" aria-required="true" data-tables='<?php echo dwspecs_get_table_groups('json'); ?>'>
				<option value=""><?php echo esc_html__('Select a table', 'product-specifications'); ?></option>
				<?php foreach( $tbl_array as $table ) {
					echo '<option value="'. esc_attr($table->ID) .'">'. esc_html($table->post_title) .'</option>';
				} ?>
			</select>
		</p>

	<?php endif; ?>

	<p>
		<label for="attr_group"><?php echo esc_html__('Attribute group : ', 'product-specifications'); ?></label>
		<select name="attr_group" id="attr_group" aria-required="true">
			<option value=""><?php echo esc_html__('Select a group', 'product-specifications'); ?></option>
			<?php
				$all_groups = get_terms( array(
					'taxonomy'   => 'spec-group',
					'hide_empty' => false,
				) );

				foreach( $all_groups as $group ){
					$selected = selected( $group->term_id, get_term_meta( $term->term_id, 'attr_group', true ) );
					$group_id = esc_attr($group->term_id);
					$group_name = esc_attr($group->name);
					echo "<option value=\"{$group_id}\" {$selected}>{$group_name}</option>";
				}
			?>
		</select>
	</p>

	<p>
		<label for="attr_type"><?php echo esc_html__('Attribute field Type : ', 'product-specifications'); ?></label>
		<select name="attr_type" id="attr_type" aria-required="true">
			<option value="" <?php selected( false, get_term_meta( $term->term_id, 'attr_type', true ) ); ?>><?php echo esc_html__('Select a field type', 'product-specifications'); ?></option>
			<option value="text" <?php selected( 'text', get_term_meta( $term->term_id, 'attr_type', true ) ); ?>><?php echo esc_html__('Text', 'product-specifications'); ?>
			<option value="select" <?php selected( 'select', get_term_meta( $term->term_id, 'attr_type', true ) ); ?>><?php echo esc_html__('Select', 'product-specifications'); ?>
			<option value="radio" <?php selected( 'radio', get_term_meta( $term->term_id, 'attr_type', true ) ); ?>><?php echo esc_html__('Radio', 'product-specifications'); ?>
			<option value="textarea" <?php selected( 'textarea', get_term_meta( $term->term_id, 'attr_type', true ) ); ?>><?php echo esc_html__('Textarea', 'product-specifications'); ?>
			<option value="true_false" <?php selected( 'true_false', get_term_meta( $term->term_id, 'attr_type', true ) ); ?>><?php echo esc_html__('True/false', 'product-specifications'); ?>
		</select>
	</p>

	<p style="display:none;">
		<label for="attr_values"><?php echo esc_html__('Values : ', 'product-specifications'); ?></label>
		<textarea name="attr_values" id="attr_values"><?php
			$attr_values = get_term_meta( $term->term_id, 'attr_values', true );
			if( is_array( $attr_values ) )
				echo esc_html(implode("\n", $attr_values));
		?></textarea>
	</p>

	<p>
		<label for="attr_default"><?php echo esc_html__('Default value : ', 'product-specifications'); ?></label>
		<span id="default_value_wrap"><input type="text" data-initial="true" name="attr_default" id="attr_default" value="<?php echo esc_attr(get_term_meta( $term->term_id, 'attr_default', true )); ?>"></span>
	</p>

	<p>
		<label for="attr_desc"><?php echo esc_html__('Description : ', 'product-specifications'); ?></label>
		<textarea name="attr_desc" id="attr_desc" placeholder="<?php echo esc_attr__('Optional', 'product-specifications'); ?>"><?php echo esc_html($term->description); ?></textarea>
	</p>

	<input type="hidden" name="action" value="dwps_modify_attributes">
	<input type="hidden" name="do" value="edit">
	<input name="id" value="<?php echo esc_attr($attribute_id); ?>" type="hidden">
	<?php wp_nonce_field( 'dwps_modify_attributes', 'dwps_modify_attributes_nonce' ); ?>
	<input type="submit" value="<?php echo esc_attr__('Update attribute', 'product-specifications'); ?>" class="button button-primary">
</form>
