<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Integration\WooCommerce;

use WooCommerce;

class WooCommerceNotInstalledNoticeHandler
{
    /**
     * Display a notice if WooCommerce is not installed.
     * This is only the case for earlier than 6.5 versions of WordPress.
     * For versions greater than 6.5, activation of the plugin won't work at all without WooCommerce.
     */
    public function __invoke(): void
    {
        if (class_exists(WooCommerce::class)) {
            return;
        }

        $this->displayNotice();
    }

    private function displayNotice(): void
    {
        add_action(
            'admin_notices',
            static function () {
                echo '<div class="notice notice-error"><p>';
                echo esc_html__('This plugin needs "WooCommerce" to run, please install "WooCommerce" first.', 'product-specifications');
                echo '</p></div>';
            }
        );
    }
}
