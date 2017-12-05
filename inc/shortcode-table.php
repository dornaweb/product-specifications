<?php
/**
 * Specifications table shortcode
 *
 * @author Am!n <www.dornaweb.com>
 * @package Wordpress
 * @subpackage Product Specifications for WooCommerce
 * @link http://www.dornaweb.com
 * @license GPL-2.0+
 * @since 0.1
*/
class dw_spec_table_shortcode extends dw_specs_front{
	/**
	 * $shortcode_tag
	 * holds the name of the shortcode tag
	 * @var string
	 */
	public $shortcode_tag = 'specs-table';

	/**
	 * __construct
	 * class constructor will set the needed filter and action hooks
	 *
	 * @param array $args
	 */
	function __construct($args = array()){
		//add shortcode
		add_shortcode( $this->shortcode_tag, array( $this, 'shortcode_handler' ) );
	}

	/**
	 * shortcode_handler
	 * @param  array  $atts shortcode attributes
	 * @param  string $content shortcode content
	 * @return string
	 */
	function shortcode_handler( $atts, $content = null ) {
		// Attributes
		extract( shortcode_atts(
			array(
				'post_id'      => '',
			), $atts )
		);

		if( empty( $post_id ) ) {
			global $post;
			if( !$post ) return;
			$post_id = $post->ID;
		}

		$table = dw_get_table_result( $post_id );

		$args = array( 'post_id' => $post_id, 'table' => $table );

		ob_start();
		DW_specs_front::get_template( 'shortcode-table-view', $args );
		$output = apply_filters( 'dw_specs_table_shortcode_output', ob_get_clean(), $args );

		return $output;
	}
}

new dw_spec_table_shortcode();