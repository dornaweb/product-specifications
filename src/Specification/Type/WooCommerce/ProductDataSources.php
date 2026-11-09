<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\WooCommerce;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use WP_Taxonomy;

final class ProductDataSources
{
    public const TAXONOMY = 'taxonomy';
    public const ATTRIBUTE = 'attribute';

    private const SHIPPING = 'shipping';
    private const IDENTIFIERS = 'identifiers';
    private const TAXONOMIES = 'taxonomies';
    private const ATTRIBUTES = 'attributes';

    /**
     * @var array<string, array{source: ProductDataSource, label: string, group: string}>|null
     */
    private ?array $fixed = null;

    /**
     * The sources that read a field rather than a named taxonomy or attribute.
     *
     * @return list<string>
     */
    public function fixedIds(): array
    {
        return array_keys($this->fixed());
    }

    public function source(SpecificationOptions $options): ?ProductDataSource
    {
        $options = SourceOptions::fromOptions($options);

        return match ($options->source()) {
            self::TAXONOMY => $options->name() === '' ? null : new TaxonomySource($options->name()),
            self::ATTRIBUTE => $options->name() === '' ? null : new AttributeSource($options->name()),
            default => $this->fixed()[$options->source()]['source'] ?? null,
        };
    }

    /**
     * What an editor can pick, from what exists on this site.
     *
     * @return list<array{source: string, name: string, label: string, group: string}>
     */
    public function choices(): array
    {
        $choices = [];

        foreach ($this->fixed() as $id => $fixed) {
            $choices[] = ['source' => $id, 'name' => '', 'label' => $fixed['label'], 'group' => $fixed['group']];
        }

        return [...$choices, ...$this->taxonomyChoices(), ...$this->attributeChoices()];
    }

    /**
     * @return array<string, array{source: ProductDataSource, label: string, group: string}>
     */
    private function fixed(): array
    {
        return $this->fixed ??= [
            'weight' => [
                'source' => new WeightSource(),
                'label' => __('Weight', 'product-specifications'),
                'group' => self::SHIPPING,
            ],
            'dimensions' => [
                'source' => new DimensionsSource(),
                'label' => __('Dimensions (L × W × H)', 'product-specifications'),
                'group' => self::SHIPPING,
            ],
            'length' => [
                'source' => new DimensionSource(Dimension::Length),
                'label' => __('Length', 'product-specifications'),
                'group' => self::SHIPPING,
            ],
            'width' => [
                'source' => new DimensionSource(Dimension::Width),
                'label' => __('Width', 'product-specifications'),
                'group' => self::SHIPPING,
            ],
            'height' => [
                'source' => new DimensionSource(Dimension::Height),
                'label' => __('Height', 'product-specifications'),
                'group' => self::SHIPPING,
            ],
            'sku' => [
                'source' => new SkuSource(),
                'label' => __('SKU', 'product-specifications'),
                'group' => self::IDENTIFIERS,
            ],
            'global_unique_id' => [
                'source' => new GlobalUniqueIdSource(),
                'label' => __('GTIN, UPC, EAN or ISBN', 'product-specifications'),
                'group' => self::IDENTIFIERS,
            ],
        ];
    }

    /**
     * Attribute taxonomies are left to the attribute source, and taxonomies without an
     * admin screen are WooCommerce's internal ones, such as product type and visibility.
     *
     * @return list<array{source: string, name: string, label: string, group: string}>
     */
    private function taxonomyChoices(): array
    {
        $choices = [];

        foreach (get_object_taxonomies('product', 'objects') as $taxonomy) {
            if (
                !$taxonomy instanceof WP_Taxonomy
                || !$taxonomy->show_ui
                || taxonomy_is_product_attribute($taxonomy->name)
            ) {
                continue;
            }

            $label = $taxonomy->labels->name ?? null;

            $choices[] = [
                'source' => self::TAXONOMY,
                'name' => $taxonomy->name,
                'label' => is_string($label) && $label !== '' ? $label : $taxonomy->name,
                'group' => self::TAXONOMIES,
            ];
        }

        return $choices;
    }

    /**
     * @return list<array{source: string, name: string, label: string, group: string}>
     */
    private function attributeChoices(): array
    {
        $choices = [];

        foreach (wc_get_attribute_taxonomies() as $attribute) {
            $name = is_object($attribute) ? ($attribute->attribute_name ?? null) : null;
            $label = is_object($attribute) ? ($attribute->attribute_label ?? null) : null;

            if (!is_string($name) || $name === '') {
                continue;
            }

            $choices[] = [
                'source' => self::ATTRIBUTE,
                'name' => wc_attribute_taxonomy_name($name),
                'label' => is_string($label) && $label !== '' ? $label : $name,
                'group' => self::ATTRIBUTES,
            ];
        }

        return $choices;
    }
}
