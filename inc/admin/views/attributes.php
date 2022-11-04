<?php
/**
 * Spec. table attributes template
 *
 * @author Am!n <www.dornaweb.com>
 * @package Wordpress
 * @subpackage Product Specifications Table Plugin
 * @link http://www.dornaweb.com
 * @license GNU General Public License v2 or later, http://www.gnu.org/licenses/gpl-2.0.html
 * @version 0.1
*/

/** Search query **/
$search_query = false;
if( isset( $_GET['q'] ) && !empty( $_GET['q'] ) ) {
	$search_query = stripcslashes( strip_tags( $_GET['q'] ) );
}

/** records per page **/
$limit = intval( get_option('dwps_view_per_page') ) ?: 15;

// just to count number of records
$att_args = array(
	'taxonomy'   => 'spec-attr',
	'hide_empty' => false,
	'search'	 => $search_query
);
if( isset( $_GET['group_id'] ) && !empty( $_GET['group_id'] ) ){
	$att_args['meta_key']   = 'attr_group';
	$att_args['meta_value'] = esc_attr( $_GET['group_id'] );
}
$all_attributes = get_terms( $att_args  );

$total_pages = sizeof( $all_attributes ) > $limit ? ceil( sizeof( $all_attributes ) / $limit ) : 1;

if( isset( $_GET['paged'] ) && !empty( $_GET['paged'] ) )
	$paged = stripcslashes( strip_tags( $_GET['paged'] ) );
else
	$paged = 1;

$offset = ( $paged - 1 ) * $limit;

$att_args = array(
	'taxonomy'   => 'spec-attr',
	'hide_empty' => false,
	'number'	 => $limit,
	'offset'	 => $offset,
	'search'	 => $search_query,
	'orderby'    => 'term_id',
	'order'		 => 'DESC'
);

if( isset( $_GET['group_id'] ) && !empty( $_GET['group_id'] ) ){
	$att_args['meta_key']   = 'attr_group';
	$att_args['meta_value'] = esc_attr( $_GET['group_id'] );
}

$tables = new WP_Query( array(
	'post_type' => 'specs-table',
	'showposts' => -1
) );
$tbl_array = $tables->get_posts();

if( isset( $_GET['table_id'] ) && !empty( $_GET['table_id'] ) ){
	$groups_flt     = dwspecs_get_table_groups( 'array', absint( $_GET['table_id'] ) );
	$groups_flt_arr = array();

	if( sizeof( $groups_flt[0]['groups'] > 0 ) ){
		foreach( $groups_flt[0]['groups'] as $grp ){
			$groups_flt_arr[] = $grp['term_id'];
		}
	}

	$att_args['meta_query']   = array(
		array(
			'key'     => 'attr_group',
			'value'   => $groups_flt_arr,
			'compare' => 'IN'
		)
	);
}


$attributes = get_terms( $att_args ); ?>

<div class="dwps-page">
	<div class="dwps-settings-wrap">
		<h3><?php _e('Attributes', 'product-specifications'); ?></h3>
		<p class="title-note"></p>

		<div class="dwps-box-wrapper clearfix">
			<div class="dwps-box-top clearfix">
				<h4><?php _e('Attributes', 'product-specifications'); ?></h4>
				<div class="dwps-group-searchform">
					<form action="<?php echo esc_url( dwspecs_current_page_url() ); ?>" method="get">
						<input type="text" name="q" value="<?php echo $search_query ?: ''; ?>" placeholder="<?php _e('Search...', 'product-specifications'); ?>">

						<?php
						if( !empty( $_GET ) ) {
							foreach( $_GET as $key => $val ){
								if( $key != 'q' ) echo "<input type=\"hidden\" name=\"$key\" value=\"$val\">";
							}
						} ?>
						<button type="submit"><i class="dashicons dashicons-search"></i></button>
					</form>
				</div><!-- .dwps-group-searchform -->

				<?php if( sizeof( $tbl_array ) > 0 ) : ?>

					<select class="dw-search-by-tbl" id="search_by_table" onChange="if (this.value) window.location.href=this.value">
						<?php
						$default_url = isset( $_GET['table_id'] ) ? esc_url( remove_query_arg( 'table_id', dwspecs_current_page_url() ) ) : ''; ?>
						<option value="<?php echo $default_url; ?>"><?php _e( 'Select a table', 'product-specifications' ); ?></option>
						<?php
						foreach( $tbl_array as $table ){
							if( isset( $_GET['table_id'] ) ) {
								$url = esc_url( add_query_arg( 'table_id', $table->ID, remove_query_arg( 'table_id', dwspecs_current_page_url() ) ) );
							} else{
								$url = esc_url( add_query_arg( 'table_id', $table->ID, esc_url( dwspecs_current_page_url() ) ) );
							}
							$selected = selected( absint( $_GET['table_id'] ), absint( $table->ID ), false );
							echo "<option value=\"{$url}\" {$selected}>{$table->post_title}</option>";
						} ?>
					</select>
				<?php endif; ?>
			</div><!-- .dwps-box-top -->

			<div id="dwps_table_wrap">
				<table class="dwps-table" id="dwps_table">
					<thead>
						<tr>
							<th class="check-column"><input type="checkbox" id="cb-select-all-1" class="selectall"></th>
							<th><?php _e('ID', 'product-specifications'); ?></th>
							<th><?php _e('Attribute name', 'product-specifications'); ?></th>
							<th><?php _e('Group name', 'product-specifications'); ?></th>
							<th><?php _e('Actions', 'product-specifications'); ?></th>
						</tr>
					</thead>

					<tbody>
						<?php
						if( sizeof( $attributes ) > 0 ) :
							foreach( $attributes as $attr ) : ?>
							<tr>
								<td class="check-column">
									<input class="dlt-bulk-group" type="checkbox" name="slct_group[]" value="<?php echo $attr->term_id; ?>">
								</td>

								<td><?php echo $attr->term_id; ?></td>

								<td><h4><a href="#" data-type="attribute" data-dwpsmodal="true" class="edit" data-id="<?php echo $attr->term_id; ?>"><?php echo $attr->name; ?></a></h4></td>

								<td>
									<h4>
										<?php
										$group = get_term( get_term_meta( $attr->term_id, 'attr_group', true ), 'spec-group' );
										if (! is_wp_error($group)) echo '<a href="'. add_query_arg( 'group_id', $group->term_id, remove_query_arg('paged', dwspecs_current_page_url()) ) .'">' . $group->name . '</a>'; ?>
									</h4>
								</td>

								<td class="dwps-actions">
									<a href="#" class="delete" data-type="attribute" data-id="<?php echo $attr->term_id; ?>"><i class="dashicons dashicons-no"></i></a>
									<a href="#" role="button" class="edit" data-dwpsmodal="true" aria-label="<?php _e('Edit group', 'product-specifications'); ?>" data-type="attribute" data-id="<?php echo $attr->term_id; ?>"><i class="dashicons dashicons-welcome-write-blog"></i></a>
								</td>

							</tr>
						<?php
							endforeach;
						else :
							echo '<tr><td class="not-found" colspan="5">' . __('Nothing found', 'product-specifications') . '</td></tr>';
						endif; ?>
					</tbody>
				</table>
			</div>
			<!-- #attributes_table_wrap -->

			<div class="dwps-buttons clearfix">
				<a id="dpws_bulk_delete_btn" class="dwps-button red-btn delete-selecteds-btn" href="#" role="button" aria-label="<?php _e('delete Selected attributes', 'product-specifications'); ?>" disabled><?php _e('Delete Selected', 'product-specifications'); ?></a>

				<a data-dwpsmodal="true" id="dpws_add_new_btn" class="dwps-button add-new-btn" href="#" role="button" aria-label="<?php _e('Add a new attribute', 'product-specifications'); ?>"><?php _e('Add new', 'product-specifications'); ?></a>

				<div class="dwps-pagination">
					<?php echo paginate_links( array(
						'base'               => '%_%',
						'format'             => '?paged=%#%',
						'total'              => $total_pages,
						'current'            => isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 0,
						'show_all'           => false,
						'end_size'           => 4,
						'mid_size'           => 2,
						'prev_next'          => true,
						'prev_text'          => __('«', 'product-specifications'),
						'next_text'          => __('»', 'product-specifications'),
						'type'               => 'plain',
						'add_args'           => false,
						'add_fragment'       => '',
						'before_page_number' => '',
						'after_page_number'  => ''
					) ); ?>
				</div><!-- .pagination -->
			</div><!-- .dwps-buttons -->

		</div><!-- .dw-box-wrapper -->
	</div><!-- .dw-specs-settings-wrap -->
</div><!-- .dwps-page -->

<script id="dwps_delete_template" type="x-tmpl-mustache" data-templateType="JSON">
	{
		"data" : {
			"type" : "attribute"
		},
		"modal" : {
			"title"	: "<?php _e("Delete Attribute", "dwspecs"); ?>",
			"content" : "<?php _e("Are you sure you want to delete selected attribute(s)?", "dwspecs"); ?>",
			"buttons" : {
				"primary": {
					"value": "<?php _e("Yes", "dwspecs"); ?>",
					"href": "#",
					"className": "modal-btn btn-confirm btn-warn"
				},
				"secondary": {
					"value": "<?php _e("No", "dwspecs"); ?>",
					"className": "modal-btn btn-cancel",
					"href": "#",
					"closeOnClick": true
				}
			}
		}
	}
</script>

<script id="dwps_texts_template" type="x-tmpl-mustache">
	{
		"add_btn"     : "<?php _e('Add', 'product-specifications'); ?>",
		"edit_btn"    : "<?php _e('Update', 'product-specifications'); ?>",
		"add_title"   : "<?php _e('Add new attribute', 'product-specifications'); ?>",
		"edit_title"  : "<?php _e('Edit attribute', 'product-specifications'); ?>"
	}
</script>

<script id="modify_form_template" type="x-tmpl-mustache">
	<form action="#" method="post" id="dwps_modify_form">
		<p>
			<label for="attr_name"><?php _e('Attribute name : ', 'product-specifications'); ?></label>
			<input type="text" name="attr_name" value="" id="attr_name" aria-required="true">
		</p>

		<p>
			<label for="attr_slug"><?php _e('Attribute slug : ', 'product-specifications'); ?></label>
			<input type="text" name="attr_slug" value="" id="attr_slug" placeholder="<?php _e('Optional', 'product-specifications'); ?>">
		</p>


		<?php if( sizeof( $tbl_array ) > 0 ) : ?>

			<p>
				<label for="attr_table"><?php _e('Table : ', 'product-specifications'); ?></label>

				<select name="attr_table" id="attr_table" aria-required="true" data-tables='<?php echo dwspecs_get_table_groups('json'); ?>'>
					<option value=""><?php _e('Select a table', 'product-specifications'); ?></option>
					<?php foreach( $tbl_array as $table ) {
						echo '<option value="'. $table->ID .'">'. $table->post_title .'</option>';
					} ?>
				</select>
			</p>

		<?php endif; ?>

		<p>
			<label for="attr_group"><?php _e('Attribute group : ', 'product-specifications'); ?></label>
			<select name="attr_group" id="attr_group" aria-required="true">
				<option value=""><?php _e('Select a group', 'product-specifications'); ?></option>
				<?php
					$all_groups = get_terms( array(
						'taxonomy'   => 'spec-group',
						'hide_empty' => false,
					) );

					foreach( $all_groups as $group ){
						echo "<option value=\"{$group->term_id}\">{$group->name}</option>";
					}
				?>
			</select>
		</p>

		<p>
			<label for="attr_type"><?php _e('Attribute field Type : ', 'product-specifications'); ?></label>
			<select name="attr_type" id="attr_type" aria-required="true">
				<option value=""><?php _e('Select a field type', 'product-specifications'); ?></option>
				<option value="text"><?php _e('Text', 'product-specifications'); ?>
				<option value="select"><?php _e('Select', 'product-specifications'); ?>
				<option value="radio"><?php _e('Radio', 'product-specifications'); ?>
				<option value="textarea"><?php _e('Textarea', 'product-specifications'); ?>
				<option value="true_false"><?php _e('True/false', 'product-specifications'); ?>
			</select>
		</p>

		<p style="display:none;">
			<label for="attr_values"><?php _e('Values : ', 'product-specifications'); ?></label>
			<textarea name="attr_values" id="attr_values"></textarea>
		</p>

		<p>
			<label for="attr_default"><?php _e('Default value : ', 'product-specifications'); ?></label>
			<span id="default_value_wrap"><input type="text" name="attr_default" id="attr_default" value=""></span>
		</p>

		<p>
			<label for="attr_desc"><?php _e('Description : ', 'product-specifications'); ?></label>
			<textarea name="attr_desc" id="attr_desc" placeholder="<?php _e('Optional', 'product-specifications'); ?>"></textarea>
		</p>

		<input type="hidden" name="action" value="dwps_modify_attributes">
		<input type="hidden" name="do" value="add">
		<?php wp_nonce_field( 'dwps_modify_attributes', 'dwps_modify_attributes_nonce' ); ?>
		<input type="submit" value="<?php _e('Add attribute', 'product-specifications'); ?>" class="button button-primary">
	</form>
</script>