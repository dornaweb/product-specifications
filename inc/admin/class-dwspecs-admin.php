<?php
/**
 * Admin area stuff
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

class DW_specs_admin{

	/**
	 * Construct
	*/
	public function __construct(){
		$this->includes();
	}

	/**
	 * include admin required files
	*/
	public function includes(){
		require_once( DWSPECS_PATH . 'inc/admin/class-dwspecs-admin-menus.php' );
		require_once( DWSPECS_PATH . 'inc/admin/class-dwspecs-admin-assets.php' );
		require_once( DWSPECS_PATH . 'inc/admin/options/class-dwspecs-admin-settings.php' );
		require_once( DWSPECS_PATH . 'inc/admin/class-dwspecs-admin-attr-groups.php' );
		require_once( DWSPECS_PATH . 'inc/admin/class-dwspecs-admin-attributes.php' );
		require_once( DWSPECS_PATH . 'inc/admin/class-dwspecs-admin-import-exports.php' );
		require_once( DWSPECS_PATH . 'inc/admin/class-dwspecs-tables.php' );
		require_once( DWSPECS_PATH . 'inc/admin/ajax.php' );
	}

	/**
	 * Get HTML template
	 *
	 * @param string $template      template Path
	 * @param array  $args          array of arguments to pass
	 * @return string
	*/
	public static function get_template( $template, $args = array() ){
		if( empty( $template ) ) return; // Ignore if $template is empty

		extract( $args );
		include( DWSPECS_PATH . 'inc/admin/' . $template . '.php' );
	}
}

new DW_specs_admin();