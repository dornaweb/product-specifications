<?php
/**
 * Plugin Name:       Product Specifications for WooCommerce
 * Plugin URI: 		  http://wwww.dornaweb.com/
 * Description:       This plugin adds a product specifications table to your woocommerce products.
 * Version:           0.2
 * Author:            Am!n A.Rezapour
 * Author URI: 		  http://www.dornaweb.com
 * License:           GPL-2.0+
 * Text Domain:       dwspecs
 * Domain Path:   	  /lang
 *
 * @link http://www.dornaweb.com
*/
define( 'DWSPECS_VERSION', '0.2' );
define( 'DWSPECS_BASENAME', plugin_basename( __FILE__ ) );
define( 'DWSPECS_PATH', plugin_dir_path( __FILE__ ) );
define( 'DWSPECS_URL', plugins_url( '', __FILE__ ) . '/' );

load_plugin_textdomain( 'dwspecs', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * @since 0.1
 */
class DW_specs
{
	/**
	 * @var $version
	*/
	public $version = '0.1';

	/**
	 * Class instance
	 *
	 * @var dw_specs
	*/
	public static $instance = null;

	/**
	 * @var stdClass
	*/
	public $options;

	/**
	 * Main Class instance
	 *
	 * @return WooCommerce - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Begin
	*/
	public function __construct() {
		$this->includes(); 				// include files
		$this->create_options(); 		// create options
		$this->handle_woocommerce();    // woocommercee handle

		// Sync group attributes when an attribute gets updated.
		add_action( 'dw_attributes_modified', array( $this, 'modified_attributes' ) );
	}

	/**
	 * Create Options as an stdClass
	*/
	public function create_options(){
		$this->options = new stdClass();
		$this->options->post_types = get_option('dwps_post_types');
		$this->options->per_page   = get_option('dwps_view_per_page');
	}

	/**
	 * Plugin activation method
	*/
	public static function plugin_activated() {
		$instance = new dw_specs;

		DW_specs_admin_settings::load_default_settings();
	}

	/**
	 * Just making some texts translatable!
	 *
	 * @since 0.1
	 */
	protected function make_dummy_texts() {
		__('Woocommerce Product Specifications', 'dwspecs');
		__('This plugin will add product specifications table to your products.', 'dwspecs');
	}

	/**
	 * Handle including required files
	*/
	public function includes(){
		require_once( DWSPECS_PATH . 'inc/dw-functions.php' );
		require_once( DWSPECS_PATH . 'inc/admin/class-dwspecs-admin.php' );
		require_once( DWSPECS_PATH . 'inc/class-dwspecs-post-types.php' );
		require_once( DWSPECS_PATH . 'inc/front.php' );
	}

	/**
	 * Do initial stuff
	*/
	private function initialize(){
		// Building menus
		$top_menu = new dw_options_page(
			array(
				'menu_title'    => __('Specification table', 'dwspecs'),
				'page_title'    => __('Product specifications table', 'dwspecs'), // page title
				'id'            => 'dw-specs', // page id ( slug )
				'description'   => __('Product specifications table for E-Commerce websites', 'dwspecs'), // some description
				'order'         => 25, // menu order
				'parent'        => false, // parent settings page id
				'icon'          => '', // icon will be displayed if parent page is empty, Uses dashicons
				'redirect'		=> true, // redirect to first child-page if options are empty
				'capability'	=> 'edit_pages' // restrict access to certain users only
			)
		);
	}

	/**
	 * Check and add specifications table to woocommerce
	*/
	public function handle_woocommerce(){
		if( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && in_array( 'product', array('product') ) ) {
			add_filter( 'woocommerce_product_tabs', array( $this, 'woocommerce_tabs' ), 98 );
		}
	}

	/**
	 * Add tables to woocommerce tabs
	*/
	public function woocommerce_tabs( $tabs ){
		$tabs['dw_product_specifications'] = array(
			'title' 	=> __( 'Product specifications', 'dwspecs' ),
			'priority' 	=> 10,
			'callback' 	=> array( $this, 'woo_display_tab' )
		);

		return $tabs;
	}

	/**
	 * Display tab content Callback
	*/
	public function woo_display_tab(){
		echo do_shortcode('[specs-table]');
	}

	/**
	 * Trigger When an attribute is added or deleted and update group attribute ids
	 *
	 * @param   Array $args
	 * @return Array
	*/
	public function modified_attributes( $args ){
		// Sync. delete action with attribute groups
		if( $args['action'] == 'delete' ){
			for( $b = 0; $b < sizeof( $args['ids'] ); $b++ ){
				$attr_id 		  = $args['ids'][$b];
				$group_id 		  = get_term_meta( $attr_id, 'attr_group', true );
				$group_attributes = get_term_meta( $group_id, 'attributes', true );

				if( is_array( $group_attributes ) && sizeof( $group_attributes ) > 0 ){
					if( in_array( $attr_id, $group_attributes ) ){
						unset( $group_attributes[ $attr_id ] );
						$updated_attributes = array_diff( $group_attributes, array($attr_id) );
						update_term_meta( $group_id, 'attributes', $updated_attributes );
					}
				} else{
					delete_term_meta( $group_id, 'attributes' );
					add_term_meta( $group_id, 'attributes', array() );
				}
			}
		} elseif( $args['action'] == 'add' || $args['action'] == 'edit' ){
			for( $i = 0; $i < sizeof( $args['ids'] ); $i++ ){
				$attr_id 		  = $args['ids'][$i];
				$group_id 		  = get_term_meta( $attr_id, 'attr_group', true );
				$group_attributes = get_term_meta( $group_id, 'attributes', true );

				if( is_array( $group_attributes ) && sizeof( $group_attributes ) > 0 ){
					if( !in_array( $attr_id, $group_attributes ) ){
						array_push( $group_attributes, $attr_id  );
						update_term_meta( $group_id, 'attributes', $group_attributes );
					}
				} else{
					delete_term_meta( $group_id, 'attributes' );
					add_term_meta( $group_id, 'attributes', array( $attr_id ) );
				}
			}
		}

	}
}

$dw_specs = new DW_specs();

// hook plugin activated!
register_activation_hook( __FILE__, array( 'dw_specs', 'plugin_activated' ) );