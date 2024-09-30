<?php

/**
 * Plugin Name: Product Specifications for WooCommerce
 * Plugin URI: https://github.com/dornaweb/product-specifications/
 * Description: This plugin adds a product specifications table to your woocommerce products.
 * Version: 0.8.2
 * Author: Amin Abdolrezapoor
 * Author URI: https://amin.nz
 * License: GPL-2.0+
 * Requires Plugins: woocommerce
 */

declare(strict_types=1);

namespace Amiut\ProductSpecs;

// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

use Inpsyde\Modularity\Package;
use Inpsyde\Modularity\Properties\PluginProperties;
use Throwable;

defined('ABSPATH') || exit;

if (!defined('DWSPECS_PLUGIN_FILE')) {
    define('DWSPECS_PLUGIN_FILE', __FILE__);
}

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
    if (class_exists(Content\Module::class)) {
        return;
    }

    if (is_readable(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
    }
}

function bootstrap(): void
{
    try {
        setupAutoLoader();

        $plugin = Package::new(
            PluginProperties::new(__FILE__)
        );

        App::instance();

        $plugin
            ->addModule(new Assets\Module())
            ->addModule(new Template\Module())
            ->addModule(new Repository\Module())
            ->addModule(new Content\Module())
            ->addModule(new Admin\Module())
            ->addModule(new AttributesListUi\Module())
            ->addModule(new AttributeGroupsListUi\Module())
            ->addModule(new ImportExport\Module())
            ->addModule(new Settings\Module())
            ->addModule(new Integration\Module())
            ->addModule(new Attribute\Module())
            ->addModule(new Shortcode\Module())
            ->addModule(new Metabox\Module())
            ->addModule(new EntityUpdater\Module())
            ->addModule(new EntityUpdaterUi\Module())
            ->addModule(new ProductSpecifications\Module())
            ->addModule(new SpecificationsTable\Module())
            ->boot();
    } catch (Throwable $throwable) {
        handleFailure($throwable);
    }
}

add_action('plugins_loaded', __NAMESPACE__ . '\\bootstrap');
