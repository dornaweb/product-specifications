<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs;

use Amiut\ProductSpecs\Block\ProductSpecifications\ProductSpecificationsBlock;
use Amiut\ProductSpecs\Storefront\ProductSpecifications;
use Amiut\ProductSpecs\Storefront\SpecificationsTable;
use WC_Product;

function specificationsTable(int|WC_Product $product): SpecificationsTable
{
    return plugin()->container()->get(ProductSpecifications::class)->table($product);
}

function hasSpecifications(int|WC_Product $product): bool
{
    return plugin()->container()->get(ProductSpecifications::class)->has($product);
}

function specificationValue(int|WC_Product $product, string $specification): mixed
{
    return plugin()->container()->get(ProductSpecifications::class)->value($product, $specification);
}

function renderSpecificationsTable(int|WC_Product $product): string
{
    return plugin()->container()->get(ProductSpecificationsBlock::class)->renderFor($product);
}
