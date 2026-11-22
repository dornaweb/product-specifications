<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Block\Comparison;

use Amiut\ProductSpecs\Comparison\ComparedProductIds;
use Amiut\ProductSpecs\Comparison\Comparison;
use Amiut\ProductSpecs\Comparison\ComparisonReader;
use Amiut\ProductSpecs\Comparison\Rest\ComparableProductsRestController;
use Amiut\ProductSpecs\Settings\SettingsRepository;
use WC_Product;

final class ComparisonBlock
{
    public const NAME = 'product-specifications/comparison';
    public const STORE = 'product-specifications/comparison';

    private ?Comparison $comparison = null;

    public function __construct(
        private readonly ComparisonReader $reader,
        private readonly SettingsRepository $settings
    ) {
    }

    public function comparison(): Comparison
    {
        // phpcs:ignore WordPress.Security -- A shareable read-only link, sanitized on the next line.
        $raw = wp_unslash($_GET[ComparedProductIds::QUERY_PARAMETER] ?? '');
        $query = is_string($raw) ? sanitize_text_field($raw) : '';

        return $this->comparison ??= $this->reader->read(ComparedProductIds::fromQuery($query), $this->maximum());
    }

    public function maximum(): int
    {
        return $this->settings->current()->maximumComparedProducts();
    }

    public function isFull(Comparison $comparison): bool
    {
        return count($comparison->products()) >= $this->maximum();
    }

    public function removeUrl(Comparison $comparison, WC_Product $product): string
    {
        return $this->urlFor($comparison->ids()->without($product->get_id()));
    }

    public function urlFor(ComparedProductIds $ids): string
    {
        $page = (string) get_permalink(get_queried_object_id());

        return $ids->isEmpty() ? $page : add_query_arg(ComparedProductIds::QUERY_PARAMETER, $ids->toQuery(), $page);
    }

    /**
     * @return array<string, mixed>
     */
    public function searchContext(Comparison $comparison): array
    {
        return [
            'set' => $comparison->setSlug(),
            'ids' => $comparison->ids()->toQuery(),
            'pageUrl' => (string) get_permalink(get_queried_object_id()),
            'isOpen' => false,
            'query' => '',
            'results' => [],
            'activeIndex' => -1,
            'status' => 'idle',
            'restoreFocus' => false,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function searchState(): array
    {
        return [
            'searchUrl' => rest_url(
                ComparableProductsRestController::NAMESPACE . ComparableProductsRestController::ROUTE
            ),
            'searching' => __('Searching…', 'product-specifications'),
            'noMatches' => __('No matching products in this set.', 'product-specifications'),
            'failed' => __('Couldn’t load products.', 'product-specifications'),
            /* translators: %d: number of matching products. */
            'found' => __('Products found: %d. Use the up and down keys to choose one.', 'product-specifications'),
        ];
    }

    public function render(): string
    {
        return render_block([
            'blockName' => self::NAME,
            'attrs' => [],
            'innerBlocks' => [],
            'innerHTML' => '',
            'innerContent' => [],
        ]);
    }
}
