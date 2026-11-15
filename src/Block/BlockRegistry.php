<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Block;

use Amiut\ProductSpecs\Settings\SettingsRepository;

final class BlockRegistry
{
    public const LOAD_STYLES_FILTER = 'product_specifications_load_styles';

    public function __construct(
        private readonly string $directory,
        private readonly SettingsRepository $settings
    ) {
    }

    public function register(): void
    {
        // Arguments override block.json, so an empty list drops the block's own stylesheet.
        $arguments = $this->loadsStyles() ? [] : ['style_handles' => []];

        foreach (glob($this->directory . '/*/block.json') ?: [] as $metadata) {
            register_block_type(dirname($metadata), $arguments);
        }
    }

    private function loadsStyles(): bool
    {
        return (bool) apply_filters(self::LOAD_STYLES_FILTER, $this->settings->current()->loadDefaultStyles());
    }
}
