<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Integration\WooCommerce;

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use Inpsyde\Modularity\Properties\PluginProperties;

class FeaturesCompatibilityDeclarations
{
    private PluginProperties $pluginProperties;

    public function __construct(PluginProperties $pluginProperties)
    {
        $this->pluginProperties = $pluginProperties;
    }

    public function __invoke()
    {
        $this->declareHposCompatibility();
    }

    private function declareHposCompatibility(): void
    {
        if (!class_exists(FeaturesUtil::class)) {
            return;
        }

        FeaturesUtil::declare_compatibility(
            'custom_order_tables',
            $this->pluginProperties->pluginMainFile(),
            true
        );
    }
}
