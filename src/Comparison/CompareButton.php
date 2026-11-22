<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Comparison;

use Amiut\ProductSpecs\Migration\MigrationState;
use Amiut\ProductSpecs\ProductValues\ProductValueRepository;
use Amiut\ProductSpecs\Settings\SettingsRepository;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetRepository;
use WC_Product;
use WC_Product_Variation;

final class CompareButton
{
    public function __construct(
        private readonly SettingsRepository $settings,
        private readonly ProductValueRepository $values,
        private readonly SpecificationSetRepository $sets,
        private readonly ProductCatalog $catalog,
        private readonly MigrationState $migration
    ) {
    }

    public function url(WC_Product $product): ?string
    {
        $pageId = $this->settings->current()->comparePageId();

        if (
            $pageId < 1
            || get_post_status($pageId) !== 'publish'
            || $product instanceof WC_Product_Variation
            || !$this->catalog->isComparable($product)
            || $this->migration->requiresMigration()
            || !$this->offersComparison($product)
        ) {
            return null;
        }

        return add_query_arg(
            ComparedProductIds::QUERY_PARAMETER,
            (string) $product->get_id(),
            (string) get_permalink($pageId)
        );
    }

    public function render(WC_Product $product): string
    {
        $url = $this->url($product);

        if ($url === null) {
            return '';
        }

        return sprintf(
            '<a class="ps-compare-button wp-element-button" href="%1$s" rel="nofollow">%2$s</a>',
            esc_url($url),
            esc_html($this->settings->current()->compareButtonLabel())
        );
    }

    private function offersComparison(WC_Product $product): bool
    {
        $setSlug = $this->values->assignedSet($product->get_id());

        $set = $setSlug === null ? null : $this->sets->bySlug($setSlug);

        return $set !== null && $set->isPublished() && $set->offersComparison();
    }
}
