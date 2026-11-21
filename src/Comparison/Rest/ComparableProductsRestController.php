<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Comparison\Rest;

use Amiut\ProductSpecs\Comparison\ComparedProductIds;
use Amiut\ProductSpecs\Comparison\ProductCatalog;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetRepository;
use WC_Product;
use WP_REST_Request;
use WP_REST_Server;

final class ComparableProductsRestController
{
    public const NAMESPACE = 'product-specifications/v2';
    public const ROUTE = '/comparison/products';

    private const RESULTS = 10;
    private const SEARCH_LENGTH = 100;

    public function __construct(
        private readonly ProductCatalog $catalog,
        private readonly SpecificationSetRepository $sets
    ) {
    }

    public function registerRoutes(): void
    {
        register_rest_route(self::NAMESPACE, self::ROUTE, [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'read'],
            'permission_callback' => '__return_true',
            'args' => [
                'set' => ['type' => 'string', 'sanitize_callback' => [self::class, 'slug']],
                'search' => ['type' => 'string', 'sanitize_callback' => [self::class, 'text']],
                'exclude' => ['type' => 'string', 'sanitize_callback' => [self::class, 'text']],
            ],
        ]);
    }

    /**
     * @return list<array{id: int, name: string, image: ?string}>
     */
    public function read(WP_REST_Request $request): array
    {
        $setSlug = $this->stringParam($request, 'set');
        $search = mb_substr(trim($this->stringParam($request, 'search')), 0, self::SEARCH_LENGTH);
        $set = $setSlug === '' ? null : $this->sets->bySlug($setSlug);

        if ($search === '' || $set === null || !$set->isPublished() || !$set->offersComparison()) {
            return [];
        }

        $products = $this->catalog->searchInSet(
            $setSlug,
            $search,
            ComparedProductIds::fromQuery($this->stringParam($request, 'exclude'))->ids(),
            self::RESULTS
        );

        return array_map(
            static fn (WC_Product $product): array => [
                'id' => $product->get_id(),
                'name' => html_entity_decode(wp_strip_all_tags($product->get_name()), ENT_QUOTES, 'UTF-8'),
                'image' => wp_get_attachment_image_url((int) $product->get_image_id(), 'woocommerce_gallery_thumbnail')
                    ?: null,
            ],
            $products
        );
    }

    public static function slug(mixed $value): string
    {
        return is_string($value) ? sanitize_title($value) : '';
    }

    public static function text(mixed $value): string
    {
        return is_string($value) ? sanitize_text_field($value) : '';
    }

    private function stringParam(WP_REST_Request $request, string $name): string
    {
        $value = $request->get_param($name);

        return is_string($value) ? $value : '';
    }
}
