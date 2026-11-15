<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport;

use RuntimeException;

final class InvalidPackage extends RuntimeException
{
    /**
     * @param list<string> $errors
     */
    public function __construct(private readonly array $errors)
    {
        parent::__construct(implode("\n", $errors));
    }

    /**
     * @return list<string>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
