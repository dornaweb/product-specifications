<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Admin;

final class WooCommerceAdminPageRegistry implements PageRegistry
{
    public function register(Page $page): void
    {
        if (!function_exists('wc_admin_register_page')) {
            return;
        }

        wc_admin_register_page([
            'id' => $page->id(),
            'title' => $page->title(),
            'parent' => $page->parent(),
            'capability' => $page->capability(),
            'nav_args' => [
                'parent' => $page->parent(),
                'order' => $page->menuOrder(),
            ],
            'path' => $page->path(),
        ]);
    }
}
