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

$groups = get_post_meta($table_id, '_groups', true);
?>

<ul class="tabs">
	<?php
	for( $i = 0; $i < sizeof( $groups ); $i++ ){
		$group = get_term_by( 'id', $groups[$i], 'spec-group' );
		if( !is_WP_Error( $group ) ) {
			$active = $i == 0 ? ' active' : '';
			echo '<li class="tab'. $active .'" data-target="#dwps_attrs_'. $group->term_id .'">' . $group->name . '</li>';
		} // !is_WP_Error
	} ?>
</ul>

<div class="tab-contents">
	<?php
	$already_has_table = dwspecs_product_has_specs_table( $post->ID );
	for( $a = 0; $a < sizeof( $groups ); $a++ ) :
		$group = get_term_by( 'id', $groups[$a], 'spec-group' );
		$attributes = get_term_meta( $group->term_id, 'attributes', true );
		if( !is_WP_Error( $group ) && is_array( $attributes ) && sizeof( $attributes ) > 0 ) : ?>
			<div class="tab-content" id="dwps_attrs_<?php echo $group->term_id; ?>">
				<ul class="attributes-list">
					<?php
					$attributes = array_values( $attributes );
					for( $b = 0; $b < sizeof( $attributes ); $b++ ) :
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
							echo "<label for=\"{$attribute->slug}\">{$attribute->name} : </label>";
							switch( $type ):
								case "text":
								default: ?>
									<input type="text" name="dw-attr[<?php echo $group->term_id; ?>][<?php echo $attribute->term_id; ?>]" id="<?php echo $attribute->slug; ?>" value="<?php echo $default; ?>">
							<?php
									break;
								case "textarea": ?>
									<textarea name="dw-attr[<?php echo $group->term_id; ?>][<?php echo $attribute->term_id; ?>]" id="<?php echo $attribute->slug; ?>"><?php echo $default; ?></textarea>
							<?php
									break;

								case "select":
								case "radio" :
									$options  = get_term_meta( $att_id, 'attr_values', true ); ?>

									<select name="dw-attr[<?php echo $group->term_id; ?>][<?php echo $attribute->term_id; ?>]" id="<?php echo $attribute->slug; ?>">
										<option value=""><?php _e( 'Select an option', 'dwspecs' ); ?></option>
										<?php
										foreach( $options as $opt ){
											echo '<option value="'. $opt .'" '. selected( $opt, $default, false ) .'>' . $opt . '</option>';
										} ?>
									</select>

									<label class="or"><?php _e('Or', 'dwspecs'); ?> <input class="customvalue-switch" type="checkbox"<?php if( $default && !in_array( $default, $options ) ) echo ' checked'; ?>></label>
									<input type="text" value="<?php if( $default && !in_array( $default, $options ) ) echo $default; ?>" name="dw-attr[<?php echo $group->term_id; ?>][<?php echo $attribute->term_id; ?>]" class="select-custom" placeholder="<?php _e('Custom value', 'dwspecs'); ?>" disabled>
							<?php
									break;
								case "true_false" : ?>
									<input class="screen-reader-text" type="checkbox" name="dw-attr[<?php echo $group->term_id; ?>][<?php echo $attribute->term_id; ?>]" value="no" checked>
									<input type="checkbox" name="dw-attr[<?php echo $group->term_id; ?>][<?php echo $attribute->term_id; ?>]" value="yes" <?php echo checked( 'yes', $default ); ?>>

									<label class="remove-checkbox-attr">
										<?php _e('Remove this field', 'dwspecs'); ?>
										<input type="checkbox" name="dw-attr[<?php echo $group->term_id; ?>][<?php echo $attribute->term_id; ?>]" value="dwspecs_chk_none" <?php echo checked( true, $field_removed ); ?>>
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
			<div class="tab-content" id="dwps_attrs_<?php echo $group->term_id; ?>"><?php _e('No attributes defined yet', 'dwspecs'); ?></div>
	<?php
		endif;
	endfor; ?>
</div><!-- .tab-contents -->