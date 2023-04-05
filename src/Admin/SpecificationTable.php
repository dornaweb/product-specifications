<?php
/**
 * Tables Stuff
 *
 * @author Dornaweb
 * @contribute Am!n <dornaweb.com>
 */

namespace Amiut\ProductSpecs\Admin;

defined('ABSPATH') || exit;

class SpecificationTable
{
	public static function init(){
		// Create meta boxes
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_box' ) );

		// Saving metaboxes
		add_action( 'save_post', array( __CLASS__, 'save_table_metabox' ) );
		add_action( 'save_post', array( __CLASS__, 'save_product_table' ) );
	}

	/**
	 * Create Metaboxes
	**/
	public static function meta_box(){
		add_meta_box( 'dw-specs-table-metas', esc_html__( 'Table Options', 'product-specifications' ), array( __CLASS__, 'content_spec_table' ), 'specs-table', 'normal', 'high' ); // Table settings in table post type

		add_meta_box( 'dwps-specs-table', esc_html__( 'Specification table', 'product-specifications' ), array( __CLASS__, 'content_product_specs' ), array( 'product' ), 'normal', 'high' ); // Table input meta boxes
	}

	/**
	 * Saving table structure
	 *
	 * @param int $post_id
	**/
	public static function save_table_metabox( $post_id ){
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if( !isset( $_POST['dwps_metabox_nonce'] ) || !wp_verify_nonce( $_POST['dwps_metabox_nonce'], 'dw-specs-table-metas' ) ) return;
		if( !current_user_can( 'edit_post', $post_id ) ) return;
		if( $_POST['post_type'] !== 'specs-table' ) return;
		if( !isset( $_POST['groups'] ) || !is_array( $_POST['groups'] ) ) return;

		$groups = array_filter( $_POST['groups'] );

		if( get_post_meta( $post_id, '_groups', true ) == '' ){
			delete_post_meta( $post_id, '_groups' );
			add_post_meta( $post_id, '_groups', $groups );
		} else{
			update_post_meta( $post_id, '_groups', $groups );
		}
	}

	/**
	 * Saving table data to product
	 *
	 * @param int $post_id
	**/
	public static function save_product_table( $post_id ){
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if( !isset( $_POST['dwps_metabox_nonce'] ) || !wp_verify_nonce( $_POST['dwps_metabox_nonce'], 'dw-specs-table-metas' ) ) return;
		if( !current_user_can( 'edit_post', $post_id ) ) return;
		if( !in_array( $_POST['post_type'], array('product') ) ) return;
		if( !isset( $_POST['specs_table'] ) ) return;

		$table_id = absint( $_POST['specs_table'] );
		update_post_meta($post_id, '_dwps_table', $table_id);

		if (!$table_id) {
			return;
		}

		$table_array = array_filter( $_POST['dw-attr'] );
		$table_result = array();

		foreach( $table_array as $group_id => $group_attributes ){
			$group = get_term_by('id', $group_id, 'spec-group' );
			$append = array(
				'group_id'    => $group_id,
				'group_name'  => $group->name,
				'group_slug'  => $group->slug,
				'group_desc'  => $group->description,
				'attributes'  => array()
			);

			foreach( $group_attributes as $attr_id => $value ){
				if( empty( $value ) || 'dwspecs_chk_none' == $value ) continue;

				$attr = get_term_by('id', $attr_id, 'spec-attr' );
				$append['attributes'][] = array(
					'attr_id'   => $attr_id,
					'attr_name' => $attr->name,
					'attr_slug' => $attr->slug,
					'attr_desc' => $attr->description,
					'value'		=> trim( $value )
				);
			}

			$table_result[] = $append;
		}

		// $table_result = serialize( $table_result );

		if( !get_post_meta( $post_id, '_dwps_specification_table', true ) || get_post_meta( $post_id, '_dwps_specification_table', true ) == '' ){
			delete_post_meta( $post_id, '_dwps_specification_table' );
			add_post_meta( $post_id, '_dwps_specification_table', $table_result );
		} else{
			if( get_post_meta( $post_id, '_dwps_specification_table', true ) != $table_result )
				update_post_meta( $post_id, '_dwps_specification_table', $table_result );
		}
	}

	/**
	 * Get list of available groups
	 *
	 * @return array
	*/
	public static function get_spec_groups(){
		$terms = get_terms( array(
			'taxonomy'   => 'spec-group',
			'hide_empty' => false
		) );

		return $terms;
	}

	/**
	 * Get list of specification tables
	 *
	 * @param string $output Output type
	 * @return Array | WP_Query
	*/
	public static function get_spec_tables( $output = 'array' ){
		$tables = new \WP_Query( array(
			'post_type' => 'specs-table',
			'showposts' => -1
		) );
		$tbl_array = $tables->get_posts();

		wp_reset_query();

		return $output == 'array' ? $tbl_array : $tables;
	}

	/**
	 * Specifications tables on products edit page
	 *
	 * @return void
	*/
	public static function content_product_specs(){
		global $post;
		$args = array(
			'tables' => self::get_spec_tables(),
			'post'	 => $post
		);

		Admin::get_template( 'views/products-specs-table', $args );
	}

	/**
	 * Specifications tables metabox content
	 *
	 * @return void
	*/
	public static function content_spec_table(){
		global $post;
		$args = array(
			'groups' => self::get_spec_groups(),
			'post'	 => $post
		);

		Admin::get_template( 'views/table-metabox', $args );
	}
}
