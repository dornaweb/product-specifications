<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ProductValues;

use Amiut\ProductSpecs\Metabox\Metabox;
use WP_Post;

final class ProductValuesMetabox implements Metabox
{
    public function id(): string
    {
        return 'product-specifications-values';
    }

    public function title(): string
    {
        return __('Product specifications', 'product-specifications');
    }

    public function context(): string
    {
        return self::CONTEXT_NORMAL;
    }

    public function priority(): string
    {
        return self::PRIORITY_HIGH;
    }

    public function enabled(WP_Post $post): bool
    {
        return $post->post_type === 'product';
    }

    public function render(WP_Post $post): string
    {
        return sprintf(
            '<div id="product-specifications-values-root" data-product-id="%d"></div>',
            $post->ID
        );
    }

    public function action(WP_Post $post): void
    {
        // The React panel writes through ProductValueSaveService over REST
        // before replaying the native product form submission.
    }
}
