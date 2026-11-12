<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ProductValues;

use WC_Product;

/**
 * The specifications a product ends with, saved by `\Amiut\ProductSpecs\saveSpecifications()`.
 *
 * An update is the product's whole value map: a specification it does not carry is removed
 * from the product when it is saved. Unless the update names a set with `withSet()` or
 * removes it with `withoutSet()`, the product keeps the set it has.
 *
 * Every method returns a new update and leaves the one it is called on unchanged.
 *
 * ```php
 * $update = SpecificationsUpdate::forProduct($product)
 *     ->withSet('laptops')
 *     ->withValues(['weight' => 1.4, 'wifi' => true]);
 * ```
 *
 * Nothing is checked while an update is built. Values are validated when it is saved, and
 * a value its specification's type rejects makes the save throw `RejectedValues`.
 */
final class SpecificationsUpdate
{
    /**
     * @param array<string, mixed> $values
     */
    private function __construct(
        private readonly int $productId,
        private readonly bool $keepsAssignedSet,
        private readonly ?string $setSlug,
        private readonly array $values
    ) {
    }

    /**
     * Starts an empty update: saved as is, it removes every value of the product and keeps
     * its set.
     *
     * @param int|WC_Product $product The product or variation to update, by id or object.
     *                                Whether it exists is checked when the update is saved.
     */
    public static function forProduct(int|WC_Product $product): self
    {
        return new self($product instanceof WC_Product ? $product->get_id() : $product, true, null, []);
    }

    /**
     * Assigns a specification set to the product.
     *
     * @param string $setSlug The slug of an existing specification set. Saving an update with
     *                        a set that does not exist throws a `RuntimeException`.
     */
    public function withSet(string $setSlug): self
    {
        return new self($this->productId, false, $setSlug, $this->values);
    }

    /**
     * Unassigns the product's specification set. A product without a set keeps no values,
     * so saving deletes all of them, and an update that also carries values is refused
     * when it is saved.
     */
    public function withoutSet(): self
    {
        return new self($this->productId, false, null, $this->values);
    }

    /**
     * Sets the values the product ends with. Calls add up: a later call overrides a key given
     * by an earlier one and keeps the rest.
     *
     * @param array<string, mixed> $values Each key is a specification key, such as
     *                                     `weight-c3dd218b`. Each value is the input that
     *                                     specification's type accepts, such as
     *                                     `true` for a yes-or-no specification or one of a
     *                                     select's choices. A key with no specification is
     *                                     ignored. A value the type rejects makes the save throw
     *                                     `RejectedValues`, naming the key.
     */
    public function withValues(array $values): self
    {
        return new self(
            $this->productId,
            $this->keepsAssignedSet,
            $this->setSlug,
            [...$this->values, ...$values]
        );
    }

    /**
     * The id of the product or variation the update is for.
     */
    public function productId(): int
    {
        return $this->productId;
    }

    /**
     * @return array<string, mixed> The values the product ends with, keyed by specification key.
     */
    public function values(): array
    {
        return $this->values;
    }

    /**
     * The set the product ends with.
     *
     * @param string|null $assignedSet The slug of the set the product has now, or null.
     * @return string|null The slug of the set the product has after the save, or null when it
     *                     ends without one.
     */
    public function resultingSet(?string $assignedSet): ?string
    {
        return $this->keepsAssignedSet ? $assignedSet : $this->setSlug;
    }
}
