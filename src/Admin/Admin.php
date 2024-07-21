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
        Ajax::init();
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
}
