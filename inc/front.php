<?php
/**
 * Front-end stuff
 *
 * @author Am!n <www.dornaweb.com>
 * @package Wordpress
 * @subpackage Product Specifications for WooCommerce
 * @link http://www.dornaweb.com
 * @license GPL-2.0+
 * @since 0.1
*/
class DW_specs_front extends dw_specs{
	function __construct(){
		$this->includes();
		add_action( 'wp_enqueue_scripts', array($this, 'assets') );
	}

	/**
	 * Front-end assets
	 */
	function assets(){
		if( ! get_option('dwps_disable_default_styles') ){
			wp_enqueue_style( 'dwspecs-front-css', DWSPECS_URL . 'assets/css/front-styles.css', array(), DWSPECS_VERSION );
		}
	}

	/**
	 * include front-end required files
	*/
	public function includes(){
		require_once( DWSPECS_PATH . 'inc/shortcode-table.php' );
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
		include( DWSPECS_PATH . 'inc/views/' . $template . '.php' );
	}
}

new DW_specs_front();