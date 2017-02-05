<?php
/**
 * Admin menus
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

class DW_specs_admin_menus{

	/**
	 * Construct
	*/
	public function __construct(){
		add_action( 'admin_menu', array( $this, 'create_menus' ) );
		add_action( 'admin_menu', array( $this, 'modify_menu_urls' ) );
	}

	/**
	 * Create menu pages
	*/
	public function create_menus(){
		// Add plugin top-level menu page
		add_menu_page(
			__('Product specifications table', 'dwspecs'),
			__('Specification table', 'dwspecs'),
			'edit_pages',
			'dw-specs',
			'',
			'dashicons-welcome-view-site',
			25
		);

		// Add tables page
		add_submenu_page(
			'dw-specs',
			__('Add a new table', 'dwspecs'),
			__('New table', 'dwspecs'),
			'edit_pages',
			'dw-specs-new',
			array( $this, 'addnew_page' )
		);

		// Groups
		add_submenu_page(
			'dw-specs',
			__('Attribute Groups', 'dwspecs'),
			__('Groups', 'dwspecs'),
			'edit_pages',
			'dw-specs-groups',
			array( $this, 'groups_page' )
		);

		// Attributes
		add_submenu_page(
			'dw-specs',
			__('Attributes', 'dwspecs'),
			__('Attributes', 'dwspecs'),
			'edit_pages',
			'dw-specs-attrs',
			array( $this, 'attributes_page' )
		);

		// Add settings page
		add_submenu_page(
			'dw-specs',
			__('Product specifications settings', 'dwspecs'),
			__('Settings', 'dwspecs'),
			'edit_pages',
			'dw-specs-settings',
			array( $this, 'settings_page' )
		);

		// Add import/export page
		/*add_submenu_page(
			'dw-specs',
			__('Product specifications Import/Export', 'dwspecs'),
			__('Import/export', 'dwspecs'),
			'edit_pages',
			'dw-specs-export',
			array( $this, 'tools_page' )
		); */
	}

	/**
	 * Modify menu page urls
	*/
	public function modify_menu_urls(){
		global $menu, $submenu;

		foreach( $submenu['dw-specs'] as $k => $d ){
			if( $d[2] == 'dw-specs-new' ){
				$submenu['dw-specs'][$k][2] = 'post-new.php?post_type=specs-table';
				break;
			}
		}
	}

	/**
	 * Create Menu page content
	 * page : add new table
	*/
	public function addnew_page(){
		echo 'Click <a href="' . admin_url('post-new.php?post_type=specs-table') . '">here</a> to create a table';
	}

	/**
	 * Create Menu page content
	 * page : groups
	*/
	public function groups_page(){
		DW_specs_admin_attr_groups::Page_HTML();
	}

	/**
	 * Create Menu page content
	 * page : attributes
	*/
	public function attributes_page(){
		DW_specs_admin_attributes::Page_HTML();
	}

	/**
	 * Create Import/export page
	*/
	public function tools_page(){
		DW_specs_admin_import_exports::Page_HTML();
	}

	/**
	 * Create Settings page
	*/
	public function settings_page(){
		DW_specs_admin_settings::Page_HTML();
	}
}

new DW_specs_admin_menus();