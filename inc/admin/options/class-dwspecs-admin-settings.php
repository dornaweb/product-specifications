<?php
/**
 * Admin Settings page
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

class DW_specs_admin_settings{

	/**
	 * Construct
	*/
	public function __construct(){
		add_action( 'admin_init', array( $this, 'settings_page_init' ) );
	}

	public static function load_default_settings(){
		$defaults = array(
			'dwps_view_per_page' => 15
		);

		foreach( $defaults as $option => $value ) {
			add_option( $option, $value );
		}
	}

	/**
	 * Create settings fields
	 *
	 * @return void
	*/
	public function settings_page_init(){
		register_setting(
            'dwps_options', // Option group
            'dwps_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

		register_setting(
            'dwps_options', // Option group
            'dwps_view_per_page', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );
	}

	/**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
	 * @return mixed sanitized $input
     */
    public function sanitize( $input ) {
        return $input;
    }

	/**
	 * Settings menu page HTML output
	*/
	public static function Page_HTML(){
		DW_specs_admin::get_template( 'options/views/settings' );
	}
}

new DW_specs_admin_settings();