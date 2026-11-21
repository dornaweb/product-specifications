<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Comparison;

use Amiut\ProductSpecs\Settings\SettingField;
use Amiut\ProductSpecs\Settings\Settings;
use Amiut\ProductSpecs\Settings\SettingSection;
use Amiut\ProductSpecs\Settings\SettingsProvider;
use Amiut\ProductSpecs\Settings\SettingType;

final class ComparisonSettings implements SettingsProvider
{
    public const PLACEMENT_AFTER_ADD_TO_CART = 'after_add_to_cart';
    public const PLACEMENT_AFTER_META = 'after_meta';
    public const PLACEMENT_NONE = 'none';

    public function fields(): array
    {
        return [
            new SettingField(
                key: Settings::COMPARE_PAGE,
                type: SettingType::PAGE,
                section: SettingSection::COMPARISON,
                label: __('Compare page', 'product-specifications'),
                description: sprintf(
                    /* translators: 1: block name, 2: shortcode. */
                    __(
                        'The page that shows the comparison. It needs the %1$s block or the %2$s shortcode.',
                        'product-specifications'
                    ),
                    '<code>' . _x('Product Comparison', 'block title', 'product-specifications') . '</code>',
                    '<code>[specs-comparison]</code>'
                ),
                default: 0
            ),
            new SettingField(
                key: Settings::COMPARE_BUTTON_PLACEMENT,
                type: SettingType::SELECT,
                section: SettingSection::COMPARISON,
                label: __('Compare button on product pages', 'product-specifications'),
                description: sprintf(
                    /* translators: 1: block name, 2: shortcode. */
                    __(
                        'Choose “Don’t add” if you want to place it manually using the %1$s block or the %2$s shortcode.',
                        'product-specifications'
                    ),
                    '<code>' . _x('Compare Button', 'block title', 'product-specifications') . '</code>',
                    '<code>[specs-compare-button]</code>'
                ),
                default: self::PLACEMENT_AFTER_ADD_TO_CART,
                options: [
                    self::PLACEMENT_AFTER_ADD_TO_CART => __('After the add-to-cart button', 'product-specifications'),
                    self::PLACEMENT_AFTER_META => __('After the product meta', 'product-specifications'),
                    self::PLACEMENT_NONE => __('Don’t add', 'product-specifications'),
                ]
            ),
            new SettingField(
                key: Settings::COMPARE_BUTTON_LABEL,
                type: SettingType::TEXT,
                section: SettingSection::COMPARISON,
                label: __('Compare button label', 'product-specifications'),
                description: __('The default label is “Compare”.', 'product-specifications'),
                default: ''
            ),
            new SettingField(
                key: Settings::COMPARE_MAX_PRODUCTS,
                type: SettingType::SELECT,
                section: SettingSection::COMPARISON,
                label: __('Maximum comparable products', 'product-specifications'),
                description: __(
                    'The maximum number of products a shopper can choose to compare side by side.',
                    'product-specifications'
                ),
                default: '3',
                options: $this->maximumOptions()
            ),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function maximumOptions(): array
    {
        $options = [];

        foreach (range(2, 10) as $count) {
            $options[$count] = (string) $count;
        }

        return $options;
    }
}
