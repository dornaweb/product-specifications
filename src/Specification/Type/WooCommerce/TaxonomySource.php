<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\WooCommerce;

use WC_Product;
use WP_Term;

final class TaxonomySource implements ProductDataSource
{
    private const SEPARATOR = ', ';

    public function __construct(private readonly string $taxonomy)
    {
    }

    /**
     * @return list<array{name: string, url: string}>|null
     */
    public function value(WC_Product $product): ?array
    {
        $terms = get_the_terms(TermOwner::of($product)->get_id(), $this->taxonomy);

        if (!is_array($terms) || $terms === []) {
            return null;
        }

        return array_map(
            static function (WP_Term $term): array {
                $link = get_term_link($term);

                return [
                    'name' => html_entity_decode($term->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    'url' => is_string($link) ? $link : '',
                ];
            },
            array_values($terms)
        );
    }

    public function plainValue(mixed $value): string
    {
        return implode(
            self::SEPARATOR,
            array_map(static fn (array $term): string => $term['name'], self::terms($value))
        );
    }

    public function render(mixed $value): string
    {
        return implode(
            self::SEPARATOR,
            array_map(
                static fn (array $term): string => $term['url'] === ''
                    ? esc_html($term['name'])
                    : sprintf('<a href="%s" rel="tag">%s</a>', esc_url($term['url']), esc_html($term['name'])),
                self::terms($value)
            )
        );
    }

    /**
     * @return list<array{name: string, url: string}>
     */
    private static function terms(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $terms = [];

        foreach ($value as $term) {
            if (is_array($term) && is_string($term['name'] ?? null) && $term['name'] !== '') {
                $terms[] = ['name' => $term['name'], 'url' => is_string($term['url'] ?? null) ? $term['url'] : ''];
            }
        }

        return $terms;
    }
}
