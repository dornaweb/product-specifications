<?php

declare(strict_types=1);

/**
 * Admin Controller
 *
 * @author Dornaweb
 * @contribute Am!n <dornaweb.com>
 */

namespace Amiut\ProductSpecs\Admin\Options;

use Amiut\ProductSpecs\Admin\Admin;

defined('ABSPATH') || exit;

class Settings
{
    /**
     * Construct
    */
    public static function init()
    {

        add_action('admin_init', [ __CLASS__, 'settings_page_init' ]);
    }

    public static function load_default_settings()
    {

        $defaults = [
            'dwps_view_per_page' => 15,
            'dwps_wc_default_specs' => 'remove_if_specs_not_empty',
        ];

        foreach ($defaults as $option => $value) {
            add_option($option, $value);
        }
    }

    /**
     * Create settings fields
     *
     * @return void
    */
    public static function settings_page_init()
    {

        register_setting(
            'dwps_options',
            'dwps_options',
            [ __CLASS__, 'sanitize' ]
        );

        register_setting(
            'dwps_options',
            'dwps_tab_title',
            [ __CLASS__, 'sanitize' ]
        );

        register_setting(
            'dwps_options',
            'dwps_view_per_page',
            [ __CLASS__, 'sanitize' ]
        );

        register_setting(
            'dwps_options',
            'dwps_wc_default_specs',
            [ __CLASS__, 'sanitize' ]
        );

        register_setting(
            'dwps_options',
            'dwps_disable_default_styles',
            [ __CLASS__, 'sanitize' ]
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     * @return mixed sanitized $input
     */
    public static function sanitize($input)
    {

        return $input;
    }

    /**
     * Settings menu page HTML output
    */
    public static function Page_HTML()
    {

        Admin::get_template('Options/views/settings');
    }
}
