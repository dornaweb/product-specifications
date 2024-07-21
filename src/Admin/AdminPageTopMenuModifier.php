<?php

namespace Amiut\ProductSpecs\Admin;

class AdminPageTopMenuModifier
{
    public function modify(): void
    {
        global $menu, $submenu;

        foreach ($submenu['dw-specs'] as $k => $d) {
            if ($d[2] === 'dw-specs-new') {
                $submenu['dw-specs'][$k][2] = 'post-new.php?post_type=specs-table';
                break;
            }
        }
    }
}
