<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Shortcode;

use Amiut\ProductSpecs\Block\Comparison\ComparisonBlock;

final class ComparisonShortcode
{
    public const KEY = 'specs-comparison';

    public function __construct(private readonly ComparisonBlock $block)
    {
    }

    public function render(): string
    {
        return $this->block->render();
    }
}
