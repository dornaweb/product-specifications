<?php
/**
 * Define post types and taxonomies
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

class DW_specs_posts_types{
	public static function init(){
		add_action( 'init', array( __CLASS__, 'taxonomies' ) ); // register taxonomies
		add_action( 'init', array( __CLASS__, 'post_types' ) ); // register post types
	}

	/**
	 * Register post types
	*/
	public static function post_types(){
		$labels = array(
			'name'                  => __( 'Specifications tables', 'dwspecs' ),
			'singular_name'         => __( 'Specifications table', 'dwspecs' ),
			'menu_name'             => __( 'Specifications table', 'dwspecs' ),
			'name_admin_bar'        => __( 'Specifications tables', 'dwspecs' ),
			'add_new'               => __( 'Add New', 'dwspecs' ),
			'add_new_item'          => __( 'Add New Spec. table', 'dwspecs' ),
			'new_item'              => __( 'New Spec. table', 'dwspecs' ),
			'edit_item'             => __( 'Edit Spec. table', 'dwspecs' ),
			'view_item'             => __( 'View Spec. table', 'dwspecs' ),
			'all_items'             => __( 'Specification tables', 'dwspecs' ),
			'search_items'          => __( 'Search Spec. tables', 'dwspecs' ),
			'parent_item_colon'     => __( 'Parent Spec. tables:', 'dwspecs' ),
			'not_found'             => __( 'No Spec. tables found.', 'dwspecs' ),
			'not_found_in_trash'    => __( 'No Spec. tables found in Trash.', 'dwspecs' ),
			'featured_image'        => _x( 'Spec. table Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'dwspecs' ),
			'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'dwspecs' ),
			'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'dwspecs' ),
			'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'dwspecs' ),
			'archives'              => _x( 'Spec. tables archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'dwspecs' ),
			'insert_into_item'      => _x( 'Insert into Spec. table', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'dwspecs' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this Spec. table', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'dwspecs' ),
			'filter_items_list'     => _x( 'Filter Spec. tables list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'dwspecs' ),
			'items_list_navigation' => _x( 'Spec. tables list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'dwspecs' ),
			'items_list'            => _x( 'Spec. tables list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'dwspecs' ),
		);

		$args = array(
			'labels'               => $labels,
			'public'               => false,
			'publicly_queryable'   => false,
			'show_ui'              => true,
			'show_in_menu'         => 'dw-specs', // 'admin.php?page=dw-specs'
			'query_var'            => false,
			'rewrite'              => array( 'slug' => 'specs-table' ),
			'capability_type'      => 'post',
			'has_archive'          => false,
			'exclude_from_search'  => true,
			'hierarchical'         => false,
			'menu_position'        => null,
			'supports'             => array( 'title' ),
		);

		register_post_type( 'specs-table', $args );
	}

	/**
	 * Register taxonomies ( spec. groups and attributes )
	*/
	public static function taxonomies(){
		$labels = array(
			'name' 				  => _x( 'Groups', 'taxonomy general name', 'dwspecs' ),
			'singular_name' 	  => _x( 'group', 'taxonomy singular name', 'dwspecs' ),
			'search_items'		  =>  __( 'Search in groups', 'dwspecs' ),
			'all_items' 		  => __( 'all groups', 'dwspecs' ),
			'parent_item' 		  => __( 'parent group', 'dwspecs' ),
			'parent_item_colon'   => __( 'parent group : ', 'dwspecs' ),
			'edit_item'			  => __( 'Edit group', 'dwspecs' ),
			'update_item' 		  => __( 'Update group', 'dwspecs' ),
			'add_new_item'		  => __( 'Add a new group', 'dwspecs' ),
			'new_item_name' 	  => __( 'New group', 'dwspecs' ),
		);
		register_taxonomy('spec-group',array('specs-table'), array(
			'show_in_menu' 		=> false,
			'show_in_nav_menus' => false,
		    'hierarchical' 		=> false,
		    'labels' 			=> $labels,
		    'show_ui' 			=> false,
		    'query_var' 		=> false,
			'public'			=> false,
		    'rewrite' 			=> false,
		) );

		$labels = array(
			'name' 				  => _x( 'Attributes', 'taxonomy general name', 'dwspecs' ),
			'singular_name' 	  => _x( 'attribute', 'taxonomy singular name', 'dwspecs' ),
			'search_items'		  =>  __( 'Search in attributes', 'dwspecs' ),
			'all_items' 		  => __( 'all attributes', 'dwspecs' ),
			'parent_item' 		  => __( 'parent attribute', 'dwspecs' ),
			'parent_item_colon'   => __( 'parent attribute : ', 'dwspecs' ),
			'edit_item'			  => __( 'Edit attribute', 'dwspecs' ),
			'update_item' 		  => __( 'Update attribute', 'dwspecs' ),
			'add_new_item'		  => __( 'Add a new attribute', 'dwspecs' ),
			'new_item_name' 	  => __( 'New attribute', 'dwspecs' ),
		);
		register_taxonomy('spec-attr',array('specs-table'), array(
			'show_in_menu' 		=> false,
			'show_in_nav_menus' => false,
		    'hierarchical' 		=> false,
		    'labels' 			=> $labels,
		    'show_ui' 			=> false,
		    'query_var' 		=> false,
			'public'			=> false,
		    'rewrite' 			=> false,
		) );
	}
}

DW_specs_posts_types::init();