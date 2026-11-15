<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type;

use Amiut\ProductSpecs\Specification\SpecificationOptions;

/**
 * For a type whose stored value refers to something local to the site, such as an
 * attachment id. An export carries the portable form, and an import turns it back into
 * what this store stores.
 */
interface PortableValue
{
    public function exportValue(mixed $stored, SpecificationOptions $options): mixed;

    /**
     * Null when nothing on this store matches the value.
     */
    public function importValue(mixed $exported, SpecificationOptions $options): mixed;
}
