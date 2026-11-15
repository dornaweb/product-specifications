<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Integration\WooCommerce;

use Amiut\ProductSpecs\Settings\SettingField;
use Amiut\ProductSpecs\Settings\Settings;
use Amiut\ProductSpecs\Settings\SettingSection;
use Amiut\ProductSpecs\Settings\SettingsProvider;
use Amiut\ProductSpecs\Settings\SettingType;

final class ProductTabSettings implements SettingsProvider
{
    public function fields(): array
    {
        return [
            new SettingField(
                key: Settings::TAB_TITLE,
                type: SettingType::TEXT,
                section: SettingSection::DISPLAY,
                label: __('Specifications tab title', 'product-specifications'),
                description: __(
                    'The heading of the specifications tab on a product page. Leave it empty to use “Specifications”.',
                    'product-specifications'
                ),
                default: ''
            ),
            new SettingField(
                key: Settings::WOOCOMMERCE_DEFAULT_SPECIFICATIONS,
                type: SettingType::SELECT,
                section: SettingSection::INTEGRATION,
                label: __('WooCommerce’s own “Additional information” tab', 'product-specifications'),
                description: __(
                    'WooCommerce shows weight, dimensions and attributes in its own tab. Choose whether it stays alongside yours.',
                    'product-specifications'
                ),
                default: 'remove_if_specs_not_empty',
                options: [
                    'remove_if_specs_not_empty' => __(
                        'Remove it when this product has specifications',
                        'product-specifications'
                    ),
                    'remove' => __('Always remove it', 'product-specifications'),
                    'keep' => __('Always keep it', 'product-specifications'),
                ]
            ),
        ];
    }
}
