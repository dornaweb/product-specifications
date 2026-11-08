<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ProductValues;

use Amiut\ProductSpecs\Migration\MigrationState;
use WP_Post;

final class ProductValuesMetabox
{
    private const ID = 'product-specifications-values';

    public function __construct(private readonly MigrationState $migration)
    {
    }

    /**
     * @wp-hook add_meta_boxes_product
     */
    public function register(): void
    {
        add_meta_box(
            self::ID,
            __('Product specifications', 'product-specifications'),
            [$this, 'render'],
            'product',
            'normal',
            'high'
        );
    }

    public function render(WP_Post $post): void
    {
        printf(
            '<div id="product-specifications-values-root" data-product-id="%d" data-migration-required="%d"></div>',
            (int) $post->ID,
            (int) $this->migration->requiresMigration()
        );
    }
}
