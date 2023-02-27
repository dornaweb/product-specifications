<?php
/**
 * Specifications table Post Types Class
 * Post types, Taxonomies, meta boxes, post columns are registered here
 *
 * @package Amiut\ProductSpecs\PostTypes
 * @since   0.1
 */

namespace Amiut\ProductSpecs;

defined('ABSPATH') || exit;

class PostTypes {
	public static function init(){
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ) ); // register taxonomies
		add_action( 'init', array( __CLASS__, 'register_post_types' ) ); // register post types
	}

	/**
	 * Register post types
	*/
	public static function register_post_types(){
		$labels = array(
			'name'                  => __( 'Specifications tables', 'product-specifications' ),
			'singular_name'         => __( 'Specifications table', 'product-specifications' ),
			'menu_name'             => __( 'Specifications table', 'product-specifications' ),
			'name_admin_bar'        => __( 'Specifications tables', 'product-specifications' ),
			'add_new'               => __( 'Add New', 'product-specifications' ),
			'add_new_item'          => __( 'Add New Spec. table', 'product-specifications' ),
			'new_item'              => __( 'New Spec. table', 'product-specifications' ),
			'edit_item'             => __( 'Edit Spec. table', 'product-specifications' ),
			'view_item'             => __( 'View Spec. table', 'product-specifications' ),
			'all_items'             => __( 'Specification tables', 'product-specifications' ),
			'search_items'          => __( 'Search Spec. tables', 'product-specifications' ),
			'parent_item_colon'     => __( 'Parent Spec. tables:', 'product-specifications' ),
			'not_found'             => __( 'No Spec. tables found.', 'product-specifications' ),
			'not_found_in_trash'    => __( 'No Spec. tables found in Trash.', 'product-specifications' ),
			'featured_image'        => _x( 'Spec. table Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'product-specifications' ),
			'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'product-specifications' ),
			'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'product-specifications' ),
			'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'product-specifications' ),
			'archives'              => _x( 'Spec. tables archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'product-specifications' ),
			'insert_into_item'      => _x( 'Insert into Spec. table', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'product-specifications' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this Spec. table', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'product-specifications' ),
			'filter_items_list'     => _x( 'Filter Spec. tables list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'product-specifications' ),
			'items_list_navigation' => _x( 'Spec. tables list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'product-specifications' ),
			'items_list'            => _x( 'Spec. tables list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'product-specifications' ),
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
	public static function register_taxonomies(){
		$labels = array(
			'name' 				  => _x( 'Groups', 'taxonomy general name', 'product-specifications' ),
			'singular_name' 	  => _x( 'group', 'taxonomy singular name', 'product-specifications' ),
			'search_items'		  =>  __( 'Search in groups', 'product-specifications' ),
			'all_items' 		  => __( 'all groups', 'product-specifications' ),
			'parent_item' 		  => __( 'parent group', 'product-specifications' ),
			'parent_item_colon'   => __( 'parent group : ', 'product-specifications' ),
			'edit_item'			  => __( 'Edit group', 'product-specifications' ),
			'update_item' 		  => __( 'Update group', 'product-specifications' ),
			'add_new_item'		  => __( 'Add a new group', 'product-specifications' ),
			'new_item_name' 	  => __( 'New group', 'product-specifications' ),
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
			'name' 				  => _x( 'Attributes', 'taxonomy general name', 'product-specifications' ),
			'singular_name' 	  => _x( 'attribute', 'taxonomy singular name', 'product-specifications' ),
			'search_items'		  =>  __( 'Search in attributes', 'product-specifications' ),
			'all_items' 		  => __( 'all attributes', 'product-specifications' ),
			'parent_item' 		  => __( 'parent attribute', 'product-specifications' ),
			'parent_item_colon'   => __( 'parent attribute : ', 'product-specifications' ),
			'edit_item'			  => __( 'Edit attribute', 'product-specifications' ),
			'update_item' 		  => __( 'Update attribute', 'product-specifications' ),
			'add_new_item'		  => __( 'Add a new attribute', 'product-specifications' ),
			'new_item_name' 	  => __( 'New attribute', 'product-specifications' ),
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
