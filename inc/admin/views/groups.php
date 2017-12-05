<?php
/**
 * Spec. table Groups template
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
$all_groups = get_terms( array(
	'taxonomy'   => 'spec-group',
	'hide_empty' => false,
	'search'	 => $search_query
) );

$total_pages = sizeof( $all_groups ) > $limit ? ceil( sizeof( $all_groups ) / $limit ) : 1;

if( isset( $_GET['paged'] ) && !empty( $_GET['paged'] ) )
	$paged = stripcslashes( strip_tags( $_GET['paged'] ) );
else
	$paged = 1;

$offset = ( $paged - 1 ) * $limit;

$groups = get_terms( array(
	'taxonomy'   => 'spec-group',
	'hide_empty' => false,
	'number'	 => $limit,
	'offset'	 => $offset,
	'search'	 => $search_query,
	'orderby'    => 'term_id',
	'order'		 => 'DESC'
) ); ?>

<div class="dwps-page">
	<div class="dwps-settings-wrap">
		<h3><?php _e('Attribute Groups', 'dwspecs'); ?></h3>
		<p class="title-note"></p>

		<div class="dwps-box-wrapper clearfix">
			<div class="dwps-box-top clearfix">
				<h4><?php _e('Attribute Groups', 'dwspecs'); ?></h4>
				<div class="dwps-group-searchform">
					<form action="<?php echo esc_url( current_page_url() ); ?>" method="get">
						<input type="text" name="q" value="<?php echo $search_query ?: ''; ?>" placeholder="<?php _e('Search...', 'dwspecs'); ?>">

						<?php
						if( !empty( $_GET ) ) {
							foreach( $_GET as $key => $val ){
								if( $key != 'q' ) echo "<input type=\"hidden\" name=\"$key\" value=\"$val\">";
							}
						} ?>
						<button type="submit"><i class="dashicons dashicons-search"></i></button>
					</form>
				</div><!-- .dwps-group-searchform -->
			</div><!-- .dwps-box-top -->

			<div id="dwps_table_wrap">
				<table class="dwps-table" id="dwps_table">
					<thead>
						<tr>
							<th class="check-column"><input type="checkbox" id="cb-select-all-1" class="selectall"></th>
							<th><?php _e('ID', 'dwspecs'); ?></th>
							<th><?php _e('Group name', 'dwspecs'); ?></th>
							<th><?php _e('Group slug', 'dwspecs'); ?></th>
							<th><?php _e('# of attributes', 'dwspecs'); ?></th>
							<th><?php _e('Actions', 'dwspecs'); ?></th>
						</tr>
					</thead>

					<tbody>
						<?php
						if( sizeof( $groups ) > 0 ) :
							foreach( $groups as $group ) :
								$attributes = dw_get_attributes_by_group( $group->term_id ); ?>
							<tr>
								<td class="check-column"><input class="dlt-bulk-group" type="checkbox" name="slct_group[]" value="<?php echo $group->term_id; ?>"></td>
								<td><?php echo $group->term_id; ?></td>
								<td><h4><a href="#" class="edit" aria-label="<?php _e('Edit group', 'dwspecs'); ?>" data-dwpsmodal="true" data-type="group" data-action="edit" data-id="<?php echo $group->term_id; ?>"><?php echo $group->name; ?></a></h4></td>
								<td><?php echo rawurldecode( $group->slug ); ?></td>
								<td><?php echo sizeof( $attributes ); ?></td>
								<td class="dwps-actions">
									<a href="#" class="delete" data-type="group" data-id="<?php echo $group->term_id; ?>"><i class="dashicons dashicons-no"></i></a>
									<a href="#" role="button" class="edit" aria-label="<?php _e('Edit group', 'dwspecs'); ?>" data-dwpsmodal="true" data-type="group" data-action="edit" data-id="<?php echo $group->term_id; ?>"><i class="dashicons dashicons-welcome-write-blog"></i></a>

									<?php
									echo '<a class="view" href="'. add_query_arg( array( 'group_id' => $group->term_id, 'page' => 'dw-specs-attrs' ), esc_url( admin_url('admin.php') ) ) .'"><i class="dashicons dashicons-visibility"></i></a>'; ?>

									<a href="#" class="re-arange" data-id="<?php echo $group->term_id; ?>"><i class="dashicons dashicons-sort"></i></a>
								</td>
							</tr>
						<?php
							endforeach;
						else :
							echo '<tr><td class="not-found" colspan="5">' . __('Nothing found', 'dwspecs') . '</td></tr>';
						endif; ?>
					</tbody>
				</table>
			</div>
			<!-- #groups_table_wrap -->

			<div class="dwps-buttons clearfix">
				<a id="dpws_bulk_delete_btn" class="dwps-button red-btn delete-selecteds-btn" href="#" role="button" aria-label="<?php _e('delete Selected attributes', 'dwspecs'); ?>" disabled><?php _e('Delete Selected', 'dwspecs'); ?></a>

				<a data-dwpsmodal="true" id="dpws_add_new_btn" class="dwps-button add-new-btn" href="#" role="button" aria-label="<?php _e('Add a new group', 'dwspecs'); ?>"><?php _e('Add new', 'dwspecs'); ?></a>

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
						'prev_text'          => __('«', 'dwspecs'),
						'next_text'          => __('»', 'dwspecs'),
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
			"type" : "group"
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
		"add_btn"     : "<?php _e('Add', 'dwspecs'); ?>",
		"edit_btn"    : "<?php _e('Update', 'dwspecs'); ?>",
		"add_title"   : "<?php _e('Add new group', 'dwspecs'); ?>",
		"edit_title"  : "<?php _e('Edit group', 'dwspecs'); ?>",
		"re_arrange"  : "<?php _e('Re arrange attributes', 'dwspecs'); ?>"
	}
</script>

<script id="modify_form_template" type="x-tmpl-mustache">
	<form action="#" method="post" id="dwps_modify_form">
		<p>
			<label for="group_name"><?php _e('Group name : ', 'dwspecs'); ?></label>
			<input type="text" name="group_name" value="" id="group_name" aria-required="true">
		</p>

		<p>
			<label for="group_slug"><?php _e('Group slug : ', 'dwspecs'); ?></label>
			<input type="text" name="group_slug" value="" id="group_slug" placeholder="<?php _e('Optional', 'dwspecs'); ?>">
		</p>

		<p>
			<label for="group_desc"><?php _e('Description : ', 'dwspecs'); ?></label>
			<textarea name="group_desc" id="group_desc" placeholder="<?php _e('Optional', 'dwspecs'); ?>"></textarea>
		</p>

		<input type="hidden" name="action" value="dwps_modify_groups">
		<input type="hidden" name="do" value="add">
		<?php wp_nonce_field( 'dwps_modify_groups', 'dwps_modify_groups_nonce' ); ?>
		<input type="submit" value="<?php _e('Add Group', 'dwspecs'); ?>" class="button button-primary">
	</form>
</script>