<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Storefront;

use Amiut\ProductSpecs\Specification\SpecificationDefinition;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use WC_Product;

final class ValueRenderer
{
    public function __construct(private readonly SpecificationTypeRegistry $types)
    {
    }

    /**
     * Output ready, escaped by the specification's type. Null when the type is unknown or
     * the product has nothing to show for the specification.
     */
    public function render(WC_Product $product, SpecificationDefinition $definition, mixed $stored): ?string
    {
        if (!$this->types->has($definition->typeId())) {
            return null;
        }

        $type = $this->types->get($definition->typeId());
        $value = $type->value($product, $stored, $definition->options());

        if ($value === null || $value === '') {
            return null;
        }

        $output = $type->render($value, $definition->options());

        return $output === '' ? null : $output;
    }
}
