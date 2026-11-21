<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Settings;

final class Settings
{
    public const TAB_TITLE = 'tab_title';
    public const WOOCOMMERCE_DEFAULT_SPECIFICATIONS = 'wc_default_specs';
    public const LOAD_DEFAULT_STYLES = 'load_default_styles';
    public const COMPARE_PAGE = 'compare_page';
    public const COMPARE_BUTTON_PLACEMENT = 'compare_button_placement';
    public const COMPARE_BUTTON_LABEL = 'compare_button_label';
    public const COMPARE_MAX_PRODUCTS = 'compare_max_products';

    public function __construct(
        private readonly string $tabTitle,
        private readonly string $wooCommerceDefaultSpecifications,
        private readonly bool $loadDefaultStyles,
        private readonly int $comparePageId,
        private readonly string $compareButtonPlacement,
        private readonly string $compareButtonLabel,
        private readonly int $maximumComparedProducts
    ) {
    }

    public function tabTitle(): string
    {
        return $this->tabTitle === '' ? __('Specifications', 'product-specifications') : $this->tabTitle;
    }

    public function wooCommerceDefaultSpecifications(): string
    {
        return $this->wooCommerceDefaultSpecifications;
    }

    public function loadDefaultStyles(): bool
    {
        return $this->loadDefaultStyles;
    }

    public function comparePageId(): int
    {
        return $this->comparePageId;
    }

    public function compareButtonPlacement(): string
    {
        return $this->compareButtonPlacement;
    }

    public function compareButtonLabel(): string
    {
        return $this->compareButtonLabel === '' ? __('Compare', 'product-specifications') : $this->compareButtonLabel;
    }

    public function maximumComparedProducts(): int
    {
        return $this->maximumComparedProducts;
    }
}
