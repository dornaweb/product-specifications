<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Block\CompareButton;

use Amiut\ProductSpecs\Comparison\CompareButton;
use WC_Product;

final class CompareButtonBlock
{
    public const NAME = 'product-specifications/compare-button';

    public function __construct(private readonly CompareButton $button)
    {
    }

    /**
     * @param array<string, mixed> $attributes
     * @param array<array-key, mixed> $context
     */
    public function button(array $attributes, array $context): string
    {
        $product = wc_get_product($this->productId($attributes, $context));

        return $product instanceof WC_Product ? $this->button->render($product) : '';
    }

    public function renderFor(int|WC_Product $product): string
    {
        return render_block([
            'blockName' => self::NAME,
            'attrs' => ['productId' => $product instanceof WC_Product ? $product->get_id() : $product],
            'innerBlocks' => [],
            'innerHTML' => '',
            'innerContent' => [],
        ]);
    }

    /**
     * A button placed in a template has no attribute and, outside a product loop, no
     * context, so it falls back to the product being viewed.
     *
     * @param array<string, mixed> $attributes
     * @param array<array-key, mixed> $context
     */
    private function productId(array $attributes, array $context): int
    {
        foreach ([$attributes['productId'] ?? null, $context['postId'] ?? null] as $candidate) {
            if (is_numeric($candidate) && (int) $candidate > 0) {
                return (int) $candidate;
            }
        }

        return (int) get_the_ID();
    }
}
