<?php

namespace Amiut\ProductSpecs\Integration\WooCommerce;

use Inpsyde\Modularity\Properties\PluginProperties;

class Assets
{
    private PluginProperties $pluginProperties;

    public function __construct(
        PluginProperties $pluginProperties
    ) {

        $this->pluginProperties = $pluginProperties;
    }

    public function load(): void
    {
        if (get_option('dwps_disable_default_styles') === 'on') {
            return;
        }

        if (! is_singular('product')) {
            return;
        }

        wp_enqueue_style(
            'dwspecs-front-css',
            untrailingslashit($this->pluginProperties->baseUrl()) . '/assets/css/front-styles.css',
            [],
            $this->pluginProperties->version()
        );
    }
}
