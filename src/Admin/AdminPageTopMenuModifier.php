<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Admin;

class AdminPageTopMenuModifier
{
    public function modify(): void
    {
        global $submenu;

        foreach ($submenu['dw-specs'] as $key => $value) {
            if ($value[2] === 'dw-specs-new') {
                $submenu['dw-specs'][$key][2] = 'post-new.php?post_type=specs-table';
                break;
            }
        }
    }
}
