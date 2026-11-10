<?php

/**
 * Plugin Name: Product Specifications for WooCommerce
 * Plugin URI: https://github.com/dornaweb/product-specifications/
 * Description: This plugin adds a product specifications table to your woocommerce products.
 * Version: 0.8.7
 * Author: Amin Abdolrezapoor
 * Author URI: https://amin.nz
 * License: GPL-2.0+
 * Requires Plugins: woocommerce
 * Requires PHP: 8.1
 * Requires at least: 7.0
 * WC tested up to: 9.3.3
 * WC requires at least: 9.2.0
 */

declare(strict_types=1);

namespace Amiut\ProductSpecs;

// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

use Inpsyde\Modularity\Package;
use Inpsyde\Modularity\Properties\PluginProperties;
use Throwable;

defined('ABSPATH') || exit;

function handleFailure(Throwable $throwable): void
{
    add_action(
        'all_admin_notices',
        static function () use ($throwable) {
            $class = 'notice notice-error';
            printf(
                '<div class="%s"><p>%s</p></div>',
                esc_attr($class),
                wp_kses_post(
                    sprintf(
                        '<strong>Error:</strong> %s <br><pre>%s</pre>',
                        $throwable->getMessage(),
                        $throwable->getTraceAsString()
                    )
                )
            );
        }
    );
}

function setupAutoLoader(): void
{
    if (class_exists(Upgrade\Installer::class)) {
        return;
    }

    if (is_readable(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
    }
}

function install(): void
{
    try {
        setupAutoLoader();

        (new Upgrade\Installer(
            new Upgrade\DataVersionRepository(),
            new Upgrade\LegacyDataProbe()
        ))->install();
    } catch (Throwable $throwable) {
        handleFailure($throwable);
    }
}

function plugin(): Package
{
    /** @var Package|null $package */
    static $package = null;

    return $package ??= Package::new(PluginProperties::new(__FILE__));
}

function bootstrap(): void
{
    try {
        setupAutoLoader();

        $booted = plugin()
            ->addModule(new Capability\Module())
            ->addModule(new Upgrade\Module())
            ->addModule(new Migration\Module())
            ->addModule(new Assets\Module())
            ->addModule(new Specification\Module())
            ->addModule(new SpecificationSet\Module())
            ->addModule(new Admin\Module())
            ->addModule(new Settings\Module())
            ->addModule(new Integration\Module())
            ->addModule(new Storefront\Module())
            ->addModule(new Block\Module())
            ->addModule(new ImportExport\Module())
            ->addModule(new Shortcode\Module())
            ->addModule(new ProductValues\Module())
            ->addModule(new Suggestion\Module())
            ->boot();

        if ($booted) {
            require_once __DIR__ . '/inc/api.php';
            do_action('product_specifications_loaded');
        }
    } catch (Throwable $throwable) {
        handleFailure($throwable);
    }
}

register_activation_hook(__FILE__, __NAMESPACE__ . '\\install');

add_action('plugins_loaded', __NAMESPACE__ . '\\bootstrap');
