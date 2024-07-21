<?php

declare(strict_types=1);

/**
 * Admin Controller
 *
 * @author Dornaweb
 * @contribute Am!n <dornaweb.com>
 */

namespace Amiut\ProductSpecs\Admin;

defined('ABSPATH') || exit;

class Admin
{
    /**
     * Init
    */
    public static function init()
    {

        add_action('admin_enqueue_scripts', [__CLASS__, 'assets']);
        Ajax::init();
        Options\Settings::init();
        ImportExport::init();
    }

    /**
     * Admin assets
     */
    public static function assets()
    {
        /** Styles **/
        wp_enqueue_style('tingle', DWSPECS_PLUGIN_URL . '/assets/css/tingle.min.css', [], DWSPECS_VERSION);
        wp_enqueue_style('dwspecs-main-admin-styles', DWSPECS_PLUGIN_URL . '/assets/css/admin-styles.css', [], DWSPECS_VERSION);

        /** Javascripts **/
        wp_enqueue_script('dwspecs-mustachejs', DWSPECS_PLUGIN_URL . '/assets/javascript/mustache.min.js', [], DWSPECS_VERSION, false);
        // wp_enqueue_script( 'dwspecs-tweenmax', DWSPECS_PLUGIN_URL . '/assets/javascript/TweenMax.min.js', array(), DWSPECS_VERSION, true );
        wp_enqueue_script('tingle', DWSPECS_PLUGIN_URL . '/assets/javascript/tingle.min.js', [], DWSPECS_VERSION, true);
        wp_enqueue_script('jquery-ui-sortable');

        wp_register_script('dwspecs-javascripts', DWSPECS_PLUGIN_URL . '/assets/javascript/admin-js.js', [], DWSPECS_VERSION, true);
        wp_localize_script('dwspecs-javascripts', 'dwspecs_plugin', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'i18n' => [
                'importing_message' => esc_html__('Importing data may take a long time, please wait...', 'product-specifications'),
                'unknown_error' => esc_html__('Something went wrong', 'product-specifications'),
            ],
        ]);
        wp_enqueue_script('dwspecs-javascripts');
    }

    /**
     * Get HTML template
     *
     * @param string $template      template Path
     * @param array  $args          array of arguments to pass
     * @return string
    */
    public static function get_template($template, $args = [])
    {

        if (empty($template)) {
            return; // Ignore if $template is empty
        }

        extract($args);
        include(DWSPECS_ABSPATH . 'src/Admin/' . $template . '.php');
    }

    /**
     * Create Import/export page
    */
    public static function tools_page()
    {

        ImportExport::Page_HTML();
    }

    /**
     * Create Settings page
    */
    public static function settings_page()
    {

        Options\Settings::Page_HTML();
    }
}
