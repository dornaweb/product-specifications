<?php
/**
 * Javascript and CSS fils load
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

class DW_specs_admin_assets{

	public function __construct(){
		add_action( 'admin_enqueue_scripts', array($this, 'backend_assets') );
	}

	public function backend_assets( $hook ){
		/** Styles **/
		wp_enqueue_style( 'dwspecs-additional-admin-styles', DWSPECS_URL . 'assets/css/admin-additionals.min.css', array(), DWSPECS_VERSION );
		wp_enqueue_style( 'dwspecs-main-admin-styles', DWSPECS_URL . 'assets/css/admin-styles.css', array(), DWSPECS_VERSION );

		/** Javascripts **/
		wp_enqueue_script( 'dwspecs-mustachejs', DWSPECS_URL . 'assets/javascript/mustache.min.js', array(), DWSPECS_VERSION, false );
		wp_enqueue_script( 'dwspecs-tweenmax', DWSPECS_URL . 'assets/javascript/TweenMax.min.js', array(), DWSPECS_VERSION, true );
		wp_enqueue_script( 'dwspecs-multimodal', DWSPECS_URL . 'assets/javascript/multi-modal.min.js', array(), DWSPECS_VERSION, true );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'dwspecs-javascripts', DWSPECS_URL . 'assets/javascript/admin-js.js', array(), DWSPECS_VERSION, true );
	}
}

new DW_specs_admin_assets();