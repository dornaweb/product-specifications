<?php
/**
 * Attributes groups menu page
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

class DW_specs_admin_attr_groups{

	/**
	 * Menu page HTML output
	*/
	public static function Page_HTML(){
		DW_specs_admin::get_template( 'views/groups' );
	}

}

new DW_specs_admin_attr_groups();