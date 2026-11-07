<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Storefront;

final class TableRow
{
    public function __construct(
        private readonly string $name,
        private readonly string $value
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * Output ready: the specification's type escaped it.
     */
    public function value(): string
    {
        return $this->value;
    }
}
