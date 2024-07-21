<?php

declare(strict_types=1);

/**
 * Attribute Groups Menu page
 *
 * @author Dornaweb
 * @contribute Am!n <dornaweb.com>
 */

namespace Amiut\ProductSpecs\Admin;

defined('ABSPATH') || exit;

class AttributeGroups
{
    /**
     * Menu page HTML output
    */
    public static function Page_HTML()
    {
        Admin::get_template('views/groups');
    }
}
