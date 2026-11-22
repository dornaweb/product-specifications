<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Shortcode;

use Amiut\ProductSpecs\Block\CompareButton\CompareButtonBlock;

final class CompareButtonShortcode
{
    public const KEY = 'specs-compare-button';

    public function __construct(private readonly CompareButtonBlock $block)
    {
    }

    public function render(mixed $attributes): string
    {
        $requested = is_array($attributes) ? ($attributes['id'] ?? null) : null;
        $productId = is_numeric($requested) ? (int) $requested : (int) get_the_ID();

        return $productId > 0 ? $this->block->renderFor($productId) : '';
    }
}
