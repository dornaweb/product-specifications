<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Settings;

final class Settings
{
    public function __construct(
        #[SettingField(
            key: 'tab_title',
            type: SettingType::TEXT,
            section: SettingSection::DISPLAY,
            label: 'Specifications tab title',
            description: 'The heading of the specifications tab on a product page. '
                . 'Leave it empty to use “Specifications”.',
            default: 'Specifications'
        )]
        private readonly string $tabTitle,
        #[SettingField(
            key: 'wc_default_specs',
            type: SettingType::SELECT,
            section: SettingSection::INTEGRATION,
            label: 'WooCommerce’s own “Additional information” tab',
            description: 'WooCommerce shows weight, dimensions and attributes in its own tab. '
                . 'Choose whether it stays alongside yours.',
            default: 'remove_if_specs_not_empty',
            options: [
                'remove_if_specs_not_empty' => 'Remove it when this product has specifications',
                'remove' => 'Always remove it',
                'keep' => 'Always keep it',
            ]
        )]
        private readonly string $wooCommerceDefaultSpecifications,
        #[SettingField(
            key: 'load_default_styles',
            type: SettingType::BOOLEAN,
            section: SettingSection::INTEGRATION,
            label: 'Plugin stylesheet',
            description: 'The plugin ships styles for the specification table. '
                . 'Turn this off if your theme styles the table itself.',
            default: true,
            controlLabel: 'Load the plugin’s stylesheet'
        )]
        private readonly bool $loadDefaultStyles
    ) {
    }

    public function tabTitle(): string
    {
        if ($this->tabTitle === '' || $this->tabTitle === 'Specifications') {
            return __('Specifications', 'product-specifications');
        }

        return $this->tabTitle;
    }

    public function wooCommerceDefaultSpecifications(): string
    {
        return $this->wooCommerceDefaultSpecifications;
    }

    public function loadDefaultStyles(): bool
    {
        return $this->loadDefaultStyles;
    }
}
