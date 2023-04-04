<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Shared\ValueObject;

class StringableValueObject
{
    protected string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }
}
