<?php
/**
 * Product Specifications main class
 *
 * @package DWSpecificationsTable
 * @since   0.1
 */

namespace DWSpecificationsTable\Shortcodes;

defined('ABSPATH') || exit;

class Table {
	/**
	 * $shortcode_tag
	 * holds the name of the shortcode tag
	 * @var string
	 */
	public static $shortcode_tag = 'specs-table';

	/**
	 * __construct
	 * class constructor will set the needed filter and action hooks
	 *
	 * @param array $args
	 */
	public static function init($args = array()){
		//add shortcode
		add_shortcode(self::$shortcode_tag, array(__CLASS__, 'shortcode_handler'));
	}

	/**
	 * shortcode_handler
	 * @param  array  $atts shortcode attributes
	 * @param  string $content shortcode content
	 * @return string
	 */
	public static function shortcode_handler( $atts, $content = null ) {
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
        
		$table = dwspecs_get_table_result( $post_id );
		$args = array( 'post_id' => $post_id, 'table' => $table );

		ob_start();
		dwspecs_table_template_part('shortcodes/table', $args);
		$output = apply_filters('dw_specs_table_shortcode_output', ob_get_clean(), $args);

		return $output;
	}
}