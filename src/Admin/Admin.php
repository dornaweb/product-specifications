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
        add_action('admin_menu', [ __CLASS__, 'create_menus' ]);
        add_action('admin_menu', [ __CLASS__, 'modify_menu_urls' ]);
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
     * Create menu pages
    */
    public static function create_menus()
    {
        // Add plugin top-level menu page
        add_menu_page(
            esc_html__('Product specifications table', 'product-specifications'),
            esc_html__('Specs. tables', 'product-specifications'),
            'edit_pages',
            'dw-specs',
            '',
            'dashicons-welcome-view-site',
            25
        );

        // Add tables page
        add_submenu_page(
            'dw-specs',
            esc_html__('Add a new table', 'product-specifications'),
            esc_html__('New table', 'product-specifications'),
            'edit_pages',
            'dw-specs-new',
            [ __CLASS__, 'addnew_page' ]
        );

        // Groups
        add_submenu_page(
            'dw-specs',
            esc_html__('Attribute Groups', 'product-specifications'),
            esc_html__('Groups', 'product-specifications'),
            'edit_pages',
            'dw-specs-groups',
            [ __CLASS__, 'groups_page' ]
        );

        // Attributes
        add_submenu_page(
            'dw-specs',
            esc_html__('Attributes', 'product-specifications'),
            esc_html__('Attributes', 'product-specifications'),
            'edit_pages',
            'dw-specs-attrs',
            [ __CLASS__, 'attributes_page' ]
        );

        // Add settings page
        add_submenu_page(
            'dw-specs',
            esc_html__('Product specifications settings', 'product-specifications'),
            esc_html__('Settings', 'product-specifications'),
            'edit_pages',
            'dw-specs-settings',
            [ __CLASS__, 'settings_page' ]
        );

        // Add import/export page
        add_submenu_page(
            'dw-specs',
            esc_html__('Product specifications Import/Export', 'product-specifications'),
            esc_html__('Import/export', 'product-specifications'),
            'edit_pages',
            'dw-specs-export',
            [ __CLASS__, 'tools_page' ]
        );
    }

    /**
     * Modify menu page urls
    */
    public static function modify_menu_urls()
    {

        global $menu, $submenu;

        foreach ($submenu['dw-specs'] as $k => $d) {
            if ($d[2] === 'dw-specs-new') {
                $submenu['dw-specs'][$k][2] = 'post-new.php?post_type=specs-table';
                break;
            }
        }
    }

    /**
     * Create Menu page content
     * page : add new table
    */
    public static function addnew_page()
    {

        echo 'Click <a href="' . esc_url(admin_url('post-new.php?post_type=specs-table')) . '">here</a> to create a table';
    }

    /**
     * Create Menu page content
     * page : groups
    */
    public static function groups_page()
    {

        AttributeGroups::Page_HTML();
    }

    /**
     * Create Menu page content
     * page : attributes
    */
    public static function attributes_page()
    {

        Attributes::Page_HTML();
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
