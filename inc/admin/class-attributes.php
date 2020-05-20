<?php
/**
 * Attributes Menu page
 *
 * @author Dornaweb
 * @contribute Am!n <dornaweb.com>
 */

namespace DWSpecificationsTable\Admin;

defined('ABSPATH') || exit;

class Attributes
{

	/**
	 * Menu page HTML output
	*/
	public static function Page_HTML(){
		Admin::get_template( 'views/attributes' );
	}

}