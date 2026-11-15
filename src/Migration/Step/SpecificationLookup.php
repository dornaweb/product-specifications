<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration\Step;

use Amiut\ProductSpecs\Specification\SpecificationDefinition;

interface SpecificationLookup
{
    public function find(int $id): ?SpecificationDefinition;

    /**
     * Recreates a deleted specification from the copy of its name, slug and description
     * that products holding a value for it still carry.
     */
    public function recreate(int $formerId, string $name, string $slug, string $description): ?SpecificationDefinition;
}
