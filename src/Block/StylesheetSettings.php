<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Block;

use Amiut\ProductSpecs\Settings\SettingField;
use Amiut\ProductSpecs\Settings\Settings;
use Amiut\ProductSpecs\Settings\SettingSection;
use Amiut\ProductSpecs\Settings\SettingsProvider;
use Amiut\ProductSpecs\Settings\SettingType;

final class StylesheetSettings implements SettingsProvider
{
    public function fields(): array
    {
        return [
            new SettingField(
                key: Settings::LOAD_DEFAULT_STYLES,
                type: SettingType::BOOLEAN,
                section: SettingSection::INTEGRATION,
                label: __('Plugin stylesheet', 'product-specifications'),
                description: __(
                    'The plugin ships styles for the specification table. Turn this off if your theme styles the table itself.',
                    'product-specifications'
                ),
                default: true,
                controlLabel: __('Load the plugin’s stylesheet', 'product-specifications')
            ),
        ];
    }
}
