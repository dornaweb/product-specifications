<?php
/**
 * Product Specifications Install class
 *
 * @package Amiut\ProductSpecs
 * @since  0.4
 */

namespace Amiut\ProductSpecs;

defined('ABSPATH') || exit;

class Install
{
    public static function install() {
        if ( ! is_blog_installed() ) {
			return;
		}

		// Check if we are not already running this routine.
		if ( 'yes' === get_transient( 'dwspecs_installing' ) ) {
			return;
        }

        set_transient( 'dwspecs_installing', 'yes', MINUTE_IN_SECONDS * 10 );

        delete_transient( 'dwspecs_installing' );

        Admin\Options\Settings::load_default_settings();
        flush_rewrite_rules();

        do_action('dwspecs_installed');
    }
}
