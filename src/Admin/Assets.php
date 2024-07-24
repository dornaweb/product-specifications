<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Admin;

use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;
use Inpsyde\Modularity\Properties\PluginProperties;
use Psr\Container\ContainerInterface;
use Inpsyde\Modularity\Module\ExecutableModule;

final class Assets
{
    private PluginProperties $pluginProperties;

    public function __construct(
        PluginProperties $pluginProperties
    ) {

        $this->pluginProperties = $pluginProperties;
    }

    public function load(): void
    {
        wp_enqueue_style(
            'tingle',
            $this->assetsDirectory() . '/css/tingle.min.css',
            [],
            $this->pluginProperties->version()
        );

        wp_enqueue_style(
            'dwspecs-main-admin-styles',
            $this->assetsDirectory() . '/css/admin-styles.css',
            [],
            $this->pluginProperties->version()
        );

        wp_enqueue_script(
            'dwspecs-mustachejs',
            $this->assetsDirectory() . '/javascript/mustache.min.js',
            [],
            $this->pluginProperties->version(),
            false
        );

        wp_enqueue_script(
            'tingle',
            $this->assetsDirectory() . '/javascript/tingle.min.js',
            [],
            $this->pluginProperties->version(),
            true
        );

        wp_enqueue_script('jquery-ui-sortable');

        wp_register_script(
            'dwspecs-javascripts',
            $this->assetsDirectory() . '/javascript/admin-js.js',
            [],
            $this->pluginProperties->version(),
            true
        );

        wp_localize_script(
            'dwspecs-javascripts',
            'dwspecs_plugin',
            [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'i18n' => [
                    'importing_message' => esc_html__('Importing data may take a long time, please wait...', 'product-specifications'),
                    'unknown_error' => esc_html__('Something went wrong', 'product-specifications'),
                ],
            ]
        );

        wp_enqueue_script('dwspecs-javascripts');
    }

    private function assetsDirectory(): string
    {
        return untrailingslashit((string) $this->pluginProperties->baseUrl()) . '/assets';
    }
}
