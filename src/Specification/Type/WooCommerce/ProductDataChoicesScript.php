<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\WooCommerce;

final class ProductDataChoicesScript
{
    private const OBJECT = 'productSpecificationsProductData';

    public function __construct(private readonly ProductDataSources $sources)
    {
    }

    public function attachTo(string $handle): void
    {
        wp_localize_script($handle, self::OBJECT, ['choices' => $this->sources->choices()]);
    }
}
