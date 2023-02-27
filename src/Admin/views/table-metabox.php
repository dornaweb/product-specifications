<?php
/**
 * Spec. table metabox template
 *
 * @author Am!n <www.dornaweb.com>
 * @package Wordpress
 * @subpackage Product Specifications Table Plugin
 * @link http://www.dornaweb.com
 * @license GNU General Public License v2 or later, http://www.gnu.org/licenses/gpl-2.0.html
 * @version 0.1
*/ ?>

<div class="dwsp-meta-wrap">
	<strong class="title"><?php _e('Attribute Groups : ', 'dwspecs'); ?></strong>
	<span class="hint"><?php _e('Select attribute groups you want to load in this table : ', 'dwspecs'); ?></span>

	<div class="dwsp-meta-item">
		<?php
		$selected_groups = get_post_meta( $post->ID, '_groups', true ) == '' ? array() : get_post_meta( $post->ID, '_groups', true );
		if( $groups && sizeof( $groups ) > 0 ) {
			foreach( $groups as $term ) :
				$slug = dwspecs_spec_group_has_duplicates( $term->name ) ? " ({$term->slug})" : "";
				$checked = in_array( $term->term_id, $selected_groups ) ? ' checked' : ''; ?>
			<p>
				<label>
					<input type="checkbox" value="<?php echo $term->term_id; ?>"<?php echo $checked; ?>>
					<span><?php echo $term->name; ?><?php echo urldecode($slug); ?></span>
				</label>
			</p>
	<?php
			endforeach;
		} else{
			_e('No Group found, Please define some groups first', 'dwspecs');
		} ?>

		<ul class="table-groups-list dpws-sortable">
			<?php
			if( is_array( $selected_groups ) && sizeof( $selected_groups ) > 0 ){
				foreach( $selected_groups as $group ) {
					$term = get_term_by( 'id', $group, 'spec-group' );
					$slug = dwspecs_spec_group_has_duplicates( $term->name ) ? " ({$term->slug})" : "";
					echo '<li><input checked type="checkbox" name="groups[]" value="'. $term->term_id .'" readonly>'. $term->name . urldecode($slug) .'</li>';
				}
			} ?>
		</ul>
	</div><!-- .dwsp-meta-item -->

	<?php wp_nonce_field( 'dw-specs-table-metas', 'dwps_metabox_nonce' ); ?>

</div><!-- .dw-specs-meta-wrap -->