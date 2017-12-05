<?php
/**
 * Import/export menu page
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

class DW_specs_admin_import_exports{

	/**
	 * Menu page HTML output
	*/
	public static function Page_HTML(){
		$tables = new WP_Query( array(
			'post_type' => 'spec-table',
			'showposts' => -1
		) );
		$tables = $tables->get_posts();
		wp_reset_postdata();

		$args = array(
			'tables'   => $tables
		);

		DW_specs_admin::get_template( 'views/tools', $args );
	}

}