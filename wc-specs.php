<?php
/**
 * Plugin Name:       Product Specifications for WooCommerce
 * Plugin URI: 		  https://wwww.dornaweb.com/
 * Description:       This plugin adds a product specifications table to your woocommerce products.
 * Version:           0.5.3
 * Author:            Am!n A.Rezapour
 * Author URI: 		  https://www.dornaweb.com
 * License:           GPL-2.0+
 * Text Domain:       dwspecs
 * Domain Path:   	  /lang
 *
 * @link http://www.dornaweb.com
*/

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

if (is_readable(__DIR__ . '/vendor/autoload.php')) {
	/* @noinspection PhpIncludeInspection */
	include_once __DIR__ . '/vendor/autoload.php';
}

if ( ! defined( 'DWSPECS_PLUGIN_FILE' ) ) {
	define( 'DWSPECS_PLUGIN_FILE', __FILE__ );
}

/**
 * Returns the main instance of PDF Gen.
 *
 * @since  0.4
 * @return Amiut\ProductSpecs\App
 */
function dwspecs_table() {
	return Amiut\ProductSpecs\App::instance();
}

// Global for backwards compatibility.
$GLOBALS['dwspecs_table'] = dwspecs_table();
