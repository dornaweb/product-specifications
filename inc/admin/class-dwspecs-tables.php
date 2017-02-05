<?php
/**
 * Table related stuff
 *
 * @author Am!n <www.dornaweb.com>
 * @package Wordpress
 * @subpackage Product Specifications for WooCommerce
 * @link http://www.dornaweb.com
 * @license GPL-2.0+
 * @since 0.1
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DW_specs_tables_stuff{
	public function __construct(){
		// Create meta boxes
		add_action( 'add_meta_boxes', array( $this, 'meta_box' ) );

		// Saving metaboxes
		add_action( 'save_post', array( $this, 'save_table_metabox' ) );
		add_action( 'save_post', array( $this, 'save_product_table' ) );
	}

	/**
	 * Create Metaboxes
	**/
	public function meta_box(){
		add_meta_box( 'dw-specs-table-metas', __( 'Table Options', 'dwspecs' ), array( $this, 'content_spec_table' ), 'specs-table', 'normal', 'high' ); // Table settings in table post type

		add_meta_box( 'dwps-specs-table', __( 'Specification table', 'dwspecs' ), array( $this, 'content_product_specs' ), array( 'product' ), 'normal', 'high' ); // Table input meta boxes
	}

	/**
	 * Saving table structure
	 *
	 * @param int $post_id
	**/
	public function save_table_metabox( $post_id ){
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if( !isset( $_POST['dwps_metabox_nonce'] ) || !wp_verify_nonce( $_POST['dwps_metabox_nonce'], 'dw-specs-table-metas' ) ) return;
		if( !current_user_can( 'edit_post' ) ) return;
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
	public function save_product_table( $post_id ){
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if( !isset( $_POST['dwps_metabox_nonce'] ) || !wp_verify_nonce( $_POST['dwps_metabox_nonce'], 'dw-specs-table-metas' ) ) return;
		if( !current_user_can( 'edit_post' ) ) return;
		if( !in_array( $_POST['post_type'], array('product') ) ) return;
		if( !isset( $_POST['specs_table'] ) || empty( $_POST['specs_table'] ) || $_POST['specs_table'] == '0' ||  $_POST['specs_table'] == 0 ) return;

		$table_id = absint( $_POST['specs_table'] );


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
				if( empty( $value ) ) continue;

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

		if( !get_post_meta( $post_id, '_dwps_table', true ) || get_post_meta( $post_id, '_dwps_table', true ) == '' ){
			delete_post_meta( $post_id, '_dwps_table' );
			add_post_meta( $post_id, '_dwps_table', $table_id );
		} else{
			if( get_post_meta( $post_id, '_dwps_table', true ) != $table_id )
				update_post_meta( $post_id, '_dwps_table', $table_id );
		}
	}

	/**
	 * Get list of available groups
	 *
	 * @return array
	*/
	public function get_spec_groups(){
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
	public function get_spec_tables( $output = 'array' ){
		$tables = new WP_Query( array(
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
	public function content_product_specs(){
		global $post;
		$args = array(
			'tables' => $this->get_spec_tables(),
			'post'	 => $post
		);

		DW_specs_admin::get_template( 'views/products-specs-table', $args );
	}

	/**
	 * Specifications tables metabox content
	 *
	 * @return void
	*/
	public function content_spec_table(){
		global $post;
		$args = array(
			'groups' => $this->get_spec_groups(),
			'post'	 => $post
		);

		DW_specs_admin::get_template( 'views/table-metabox', $args );
	}
}
new DW_specs_tables_stuff();