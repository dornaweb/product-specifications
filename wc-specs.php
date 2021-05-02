<?php
/**
 * Plugin Name:       Product Specifications for WooCommerce
 * Plugin URI: 		  https://wwww.dornaweb.com/
 * Description:       This plugin adds a product specifications table to your woocommerce products.
 * Version:           0.4.3
 * Author:            Am!n A.Rezapour
 * Author URI: 		  https://www.dornaweb.com
 * License:           GPL-2.0+
 * Text Domain:       dwspecs
 * Domain Path:   	  /lang
 *
 * @link http://www.dornaweb.com
*/

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'DWSPECS_PLUGIN_FILE' ) ) {
	define( 'DWSPECS_PLUGIN_FILE', __FILE__ );
}

/**
 * Load core packages and the autoloader.
 * The SPL Autoloader needs PHP 5.6.0+ and this plugin won't work on older versions
 */
if (version_compare(PHP_VERSION, '5.6.0', '>=')) {
	require __DIR__ . '/inc/class-autoloader.php';
}

/**
 * Returns the main instance of PDF Gen.
 *
 * @since  0.4
 * @return DWSpecificationsTable\App
 */
function dwspecs_table() {
	return DWSpecificationsTable\App::instance();
}

// Global for backwards compatibility.
$GLOBALS['dwspecs_table'] = dwspecs_table();
