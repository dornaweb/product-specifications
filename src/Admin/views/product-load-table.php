<?php
/**
 * Load specs. table
 *
 * @author Am!n <www.dornaweb.com>
 * @package Wordpress
 * @subpackage Product Specifications Table Plugin
 * @link http://www.dornaweb.com
 * @license GNU General Public License v2 or later, http://www.gnu.org/licenses/gpl-2.0.html
 * @version 0.1
*/

$groups = array_filter((array) get_post_meta($table_id, '_groups', true));
?>

<ul class="tabs">
	<?php
	for( $i = 0; $i < count( $groups ); $i++ ){
		$group = get_term_by( 'id', $groups[$i], 'spec-group' );
		if( !is_WP_Error( $group ) ) {
			$active = $i == 0 ? ' active' : '';
			echo '<li class="tab'. esc_attr($active) .'" data-target="#dwps_attrs_'. esc_attr($group->term_id) .'">' . esc_html($group->name) . '</li>';
		} // !is_WP_Error
	} ?>
</ul>

<div class="tab-contents">
	<?php
	$already_has_table = dwspecs_product_has_specs_table( $post->ID );
	for( $a = 0; $a < count( $groups ); $a++ ) :
		$group = get_term_by( 'id', $groups[$a], 'spec-group' );
		$attributes = get_term_meta( $group->term_id, 'attributes', true );

		if( !is_WP_Error( $group ) && is_array( $attributes ) && count( $attributes ) > 0 ) : ?>
			<div class="tab-content" id="dwps_attrs_<?php echo esc_attr($group->term_id); ?>">
				<ul class="attributes-list">
					<?php
					$attributes = array_values( $attributes );
					for( $b = 0; $b < count( $attributes ); $b++ ) :
						$att_id = $attributes[$b];
						$attribute   = get_term_by('id', $att_id, 'spec-attr');
						$type 	     = get_term_meta( $att_id, 'attr_type', true );
						$default     = get_term_meta( $att_id, 'attr_default', true ) ?: '';
						$fill        = dwspecs_attr_value_by( $post->ID, 'id', $att_id );

						$field_removed	 = ! $fill && $already_has_table;

						if( $fill ){
							$default = $fill['value'];
						}

						if( !is_WP_Error( $attribute ) ) :
							echo "<li class=\"dw-table-field-wrap\">";
							$attribute_slug = esc_attr($attribute->slug);
							$attribute_name = esc_attr($attribute->name);
							echo "<label for=\"{$attribute_slug}\">{$attribute_name} : </label>";
							switch( $type ):
								case "text":
								default: ?>
									<input type="text" name="<?php echo sprintf("dw-attr[%s][%s]", esc_attr($group->term_id), esc_attr($attribute->term_id)); ?>" id="<?php echo esc_attr($attribute->slug); ?>" value="<?php echo esc_attr($default); ?>">
							<?php
									break;
								case "textarea": ?>
									<textarea name="<?php echo sprintf("dw-attr[%s][%s]", esc_attr($group->term_id), esc_attr($attribute->term_id)); ?>" id="<?php echo esc_attr($attribute->slug); ?>"><?php echo esc_html($default); ?></textarea>
							<?php
									break;

								case "select":
								case "radio" :
									$options  = get_term_meta( $att_id, 'attr_values', true ); ?>

									<select
										name="<?php echo sprintf("dw-attr[%s][%s]", esc_attr($group->term_id), esc_attr($attribute->term_id)); ?>"
										id="<?php echo esc_attr($attribute->slug); ?>"
										<?php if( $default && !in_array( $default, $options ) ) echo "disabled"; ?>
									>
										<option value=""><?php echo esc_html__( 'Select an option', 'product-specifications' ); ?></option>
										<?php
										foreach( $options as $opt ){
											echo '<option value="'. esc_attr($opt) .'" '. selected( $opt, $default, false ) .'>' . esc_html($opt) . '</option>';
										} ?>
									</select>

									<label class="or"><?php echo esc_html__('Or', 'product-specifications'); ?> <input class="customvalue-switch" type="checkbox"<?php if( $default && !in_array( $default, $options ) ) echo ' checked'; ?>></label>
									<input
										type="text"
										value="<?php if( $default && !in_array( $default, $options ) ) echo esc_attr($default); ?>"
										name="<?php echo sprintf("dw-attr[%s][%s]", esc_attr($group->term_id), esc_attr($attribute->term_id)); ?>"
										class="select-custom"
										placeholder="<?php echo esc_html__('Custom value', 'product-specifications'); ?>"
										<?php if( !$default || in_array( $default, $options ) ) echo 'disabled'; ?>
									>
							<?php
									break;
								case "true_false" : ?>
									<input class="screen-reader-text" type="checkbox" name="<?php echo sprintf("dw-attr[%s][%s]", esc_attr($group->term_id), esc_attr($attribute->term_id)); ?>" value="no" checked>
									<input type="checkbox" name="<?php echo sprintf("dw-attr[%s][%s]", esc_attr($group->term_id), esc_attr($attribute->term_id)); ?>" value="yes" <?php checked( 'yes', $default ); ?>>

									<label class="remove-checkbox-attr">
										<?php echo esc_html__('Remove this field', 'product-specifications'); ?>
										<input type="checkbox" name="<?php echo sprintf("dw-attr[%s][%s]", esc_attr($group->term_id), esc_attr($attribute->term_id)); ?>" value="dwspecs_chk_none" <?php checked( true, $field_removed ); ?>>
									</label>
							<?php
									break;
							endswitch; ?>

						<?php
							echo "</li>";
						endif;
					endfor; ?>
				</ul>
			</div><!-- .tab-content -->

	<?php
		else : ?>
			<div class="tab-content" id="dwps_attrs_<?php echo esc_attr($group->term_id); ?>"><?php echo esc_html__('No attributes defined yet', 'product-specifications'); ?></div>
	<?php
		endif;
	endfor; ?>
</div><!-- .tab-contents -->
