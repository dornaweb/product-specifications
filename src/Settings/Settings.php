<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Settings;

final class Settings
{
    public const TAB_TITLE = 'tab_title';
    public const WOOCOMMERCE_DEFAULT_SPECIFICATIONS = 'wc_default_specs';
    public const LOAD_DEFAULT_STYLES = 'load_default_styles';

    public function __construct(
        private readonly string $tabTitle,
        private readonly string $wooCommerceDefaultSpecifications,
        private readonly bool $loadDefaultStyles
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
}
