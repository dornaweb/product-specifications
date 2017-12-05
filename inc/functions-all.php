<?php
/**
 * Functions
 *
 * @author Am!n <www.dornaweb.com>
 * @package Wordpress
 * @subpackage Product Specifications for WooCommerce
 * @link http://www.dornaweb.com
 * @license GPL-2.0+
 * @since 0.1
*/

/**
 * Var_dump pre-ed!
 * For debugging purposes
 *
 * @param mixed $val desired variable to var_dump
 * @uses var_dump
 *
 * @return string
*/
if( !function_exists('dumpit') ) {
	function dumpit( $val ) {
		echo '<pre style="direction:ltr;text-align:left;">';
		var_dump( $val );
		echo '</pre>';
	}
}

/**
 * current_page_url
 *
 * @return string
*/
if( !function_exists('current_page_url') ) {
	function current_page_url() {
		$pageURL = 'http';
		if( isset($_SERVER["HTTPS"]) ) {
			if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}
}

/**
 * Strip certain tags from a string
 *
 * @param string $string the string you want to strip
 * @param array $tags array of tags you want to strip
 * @return string
*/
if( !function_exists('dw_strip_some') ) {
	function dw_strip_some( $string, $tags = array() ) {
		foreach ($tags as $tag) {
			$string = preg_replace('/<\/?' . $tag . '(.|\s)*?>/', '', $string);
		}

		return $string;
	}
}

/**
 * Shorten a string by words count
 *
 * @param string $string desired string
 * @param int $words number of words you want to keep
 * @return string
 */
if( !function_exists('dw_cut_words') ) {
	function dw_cut_words( $string = '', $words ='35' ) {
		$string = strip_tags(strip_shortcodes($string));
		$allwords = explode(' ', $string, $words + 1);

		if( count($allwords) > $words ){
			array_pop($allwords);
			array_push($allwords, '…');
			$string = implode(' ', $allwords);
		}

		return $string;
	}
}

/**
 * Get the list of attributes related to a group
 *
 * @param int $group_id id of the group
 * @return Array
 */
if( !function_exists('dw_get_attributes_by_group') ) {
	function dw_get_attributes_by_group( $group_id = false ){
		if( !$group_id ) return;

		return get_terms( array(
			'taxonomy'   => 'spec-attr',
			'hide_empty' => false,
			'meta_key'   => 'attr_group',
			'meta_value' => $group_id
		) );
	}
}

/**
 * encodeURIComponent as Javascript does.
 *
 * @param string $str
 * @return string
 */
if( !function_exists('encodeURIComponent') ) {
	function encodeURIComponent($str) {
	    $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
	    return strtr(rawurlencode($str), $revert);
	}
}

/**
 * encodeURIComponent as Javascript does.
 *
 * @param  int     $post_id
 * @return string  $output ( 'serialized'|'array'|'json' )
 * @return mixed   Table result
 */
if( !function_exists('dw_get_table_result') ){
	function dw_get_table_result( $post_id = '', $output = 'array', $hide_empty = true ) {
		if( !$post_id ){
			global $post;
			$post_id = $post->ID;
		}

		if( !$post_id ) return;

		$table = get_post_meta( $post_id, '_dwps_specification_table', true );

		if( !$table || empty( $table ) ) return;

		// unserialize
		//$result = unserialize( $table );
		$result = $table;

		if( $hide_empty ){
			$result = array_filter( $result, function( $v ){
				return sizeof( $v['attributes'] ) > 0;
			} );
		}

		if( $output == 'serialized' )
			$result = serialize( $result );
		elseif( $output == 'json' )
			$result = json_encode( $result, JSON_PRETTY_PRINT );

		return $result;
	}
}

/**
 * Get attribute value by id|slug|name
 *
 * @param  string $field ( 'id'|'slug'|'name' )
 * @param  mixed  $value id or slug or name
 * @return mixed
*/
if( !function_exists('dw_attr_value_by') ){
	function dw_attr_value_by( $post_id = '', $field, $value ) {
		if( !$post_id ){
	        global $post;
	        $post_id = $post->ID;
	    }

	    if( !$post_id ) return;

	    $table = dw_get_table_result( $post_id ); // The large array

		if( !is_array( $table ) ) return false;

	    foreach( $table as $groupKey => $group) {
	        if (isset($group['attributes'])) {
	            foreach ($group['attributes'] as $attr) {
	                if( $field == 'id' && $attr['attr_id'] == $value ){
	                    return $attr;
	                } elseif( $field == 'slug' && $attr['attr_slug'] == rawurlencode($value) ){
	                    return $attr;
	                } elseif( $field == 'name' && $attr['attr_name'] == $value ){
	                    return $attr;
	                }
	            }
	        }
	    }

	    return null;
	}
}

/**
 * Get list of groups of a table
 *
 * @param string $format  array|JSON
 * @param int $table_id
 * @return mixed
*/
if( !function_exists('dw_get_table_groups') ){
	function dw_get_table_groups( $format = 'array', $table_id = false ){
		$output = array();
		if( !$table_id ) {
			$tables = new WP_Query( array(
				'post_type' => 'specs-table',
				'showposts' => -1
			) );
			$tbl_array = $tables->get_posts();

			foreach( $tbl_array as $table ){
				$groups = get_post_meta( $table->ID, '_groups', true ) == '' ? array() : get_post_meta( $table->ID, '_groups', true );
				$groups_array = array();

				foreach( $groups as $group ){
					$group = get_term_by('id', $group, 'spec-group');
					$groups_array[] = array(
						'name'    => $group->name,
						'term_id' => $group->term_id,
						'slug'    => $group->slug
					);
				}

				$output[] = array(
					'table_id' => $table->ID,
					'groups'   => $groups_array
				);

			}
		} elseif( absint( $table_id ) !== 0 ){
			$groups = get_post_meta( $table_id, '_groups', true ) == '' ? array() : get_post_meta( $table_id, '_groups', true );
			$groups_array = array();

			foreach( $groups as $group ){
				$group = get_term_by('id', $group, 'spec-group');
				$groups_array[] = array(
					'name'    => $group->name,
					'term_id' => $group->term_id,
					'slug'    => $group->slug
				);
			}

			$output[] = array(
				'table_id' => $table_id,
				'groups'   => $groups_array
			);
		}

		if( $format == 'json' ) {
			return json_encode( $output );
		} else{
			return $output;
		}
	}
}

if( ! function_exists('dw_product_has_specs_table') ){
	function dw_product_has_specs_table( $post_id = '' ){
		if( ! $post_id ){
			global $post;
			$post_id = $post->ID;
		}

		return boolval( get_post_meta( $post_id, '_dwps_specification_table', true ) );
	}
}

if( ! function_exists('dw_spec_group_has_duplicates') ){
	function dw_spec_group_has_duplicates( $name, $tax = 'spec-group' ){
		if( ! $name ) return false;

		$terms = get_terms(array(
			'taxonomy'	 => $tax,
			'hide_empty' => false,
			'name'		 => $name
		));

		return count( $terms ) > 1;
	}
}