<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs;

use Amiut\ProductSpecs\Block\ProductSpecifications\ProductSpecificationsBlock;
use Amiut\ProductSpecs\ProductValues\RejectedValues;
use Amiut\ProductSpecs\ProductValues\SpecificationsUpdate;
use Amiut\ProductSpecs\ProductValues\SpecificationsUpdater;
use Amiut\ProductSpecs\Storefront\ProductSpecifications;
use Amiut\ProductSpecs\Storefront\SpecificationsTable;
use RuntimeException;
use WC_Product;

/**
 * The product's specifications table, as the storefront shows it, after the
 * `product_specifications_table` filter.
 *
 * @param int|WC_Product $product The product or variation, by id or object.
 * @return SpecificationsTable The table's groups and rows. Empty when the product does not
 *                             exist, has no specifications, or the migration is not complete.
 */
function specificationsTable(int|WC_Product $product): SpecificationsTable
{
    return plugin()->container()->get(ProductSpecifications::class)->table($product);
}

/**
 * Whether the product's specifications table has anything to show.
 *
 * @param int|WC_Product $product The product or variation, by id or object.
 * @return bool False when `specificationsTable()` returns an empty table.
 */
function hasSpecifications(int|WC_Product $product): bool
{
    return plugin()->container()->get(ProductSpecifications::class)->has($product);
}

/**
 * One specification's value on a product.
 *
 * @param int|WC_Product $product The product or variation, by id or object.
 * @param string $specification The specification key, such as `weight-c3dd218b`.
 * @return mixed The value as the specification's type reads it, such as a boolean for a
 *               yes-or-no specification. Null when the product holds none, the product or
 *               specification does not exist, or the migration is not complete.
 */
function specificationValue(int|WC_Product $product, string $specification): mixed
{
    return plugin()->container()->get(ProductSpecifications::class)->value($product, $specification);
}

/**
 * The product's specifications table as HTML, rendered by the block as the storefront
 * shows it.
 *
 * @param int|WC_Product $product The product or variation, by id or object.
 * @return string Output-ready HTML. Empty when there is nothing to show.
 */
function renderSpecificationsTable(int|WC_Product $product): string
{
    return plugin()->container()->get(ProductSpecificationsBlock::class)->renderFor($product);
}

/**
 * Saves the update as the product's whole value map: a specification it does not carry is
 * removed. Values are validated as in the product editor, and nothing is written when the
 * save fails. A save fires `product_specifications_values_saved`. No capability is checked;
 * the calling code decides who may write.
 *
 * @param SpecificationsUpdate $update The product, its set and its values. See
 *                                     `SpecificationsUpdate` for how to build one.
 * @return array<string, mixed> The stored value map, keyed by specification key, as each
 *                              type sanitized it.
 * @throws RejectedValues When a specification's type rejects its value. `messages()` holds
 *                        each message, keyed by specification key.
 * @throws RuntimeException When the product is not a product or variation, the set does not
 *                          exist, the update carries values but leaves the product without a
 *                          set, the migration is not complete, or storage fails.
 */
function saveSpecifications(SpecificationsUpdate $update): array
{
    return plugin()->container()->get(SpecificationsUpdater::class)->save($update);
}
