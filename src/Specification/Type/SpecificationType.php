<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use WC_Product;

interface SpecificationType
{
    public function id(): string;

    public function label(): string;

    /**
     * @return array<string, mixed>
     */
    public function optionsSchema(): array;

    /**
     * Null is an empty value, not a refusal to store.
     */
    public function sanitize(mixed $input): mixed;

    public function value(WC_Product $product, mixed $stored): mixed;

    /**
     * Output ready. The type escapes everything it returns.
     */
    public function render(mixed $value, SpecificationOptions $options): string;
}
