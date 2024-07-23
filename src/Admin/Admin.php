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
    public static function get_template($template, $args = [])
    {

        if (empty($template)) {
            return; // Ignore if $template is empty
        }

        extract($args);
        include(DWSPECS_ABSPATH . 'src/Admin/' . $template . '.php');
    }
}
