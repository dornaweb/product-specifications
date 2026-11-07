<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Shortcode;

use Amiut\ProductSpecs\Block\ProductSpecifications\ProductSpecificationsBlock;

final class SpecificationsTable
{
    public const KEY = 'specs-table';

    public function __construct(private readonly ProductSpecificationsBlock $block)
    {
    }

    public function render(mixed $attributes): string
    {
        $requested = is_array($attributes) ? ($attributes['post_id'] ?? null) : null;
        $postId = is_numeric($requested) ? (int) $requested : (int) get_the_ID();

        return $postId > 0 ? $this->block->renderFor($postId) : '';
    }
}
