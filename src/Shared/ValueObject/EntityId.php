<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Shared\ValueObject;

use InvalidArgumentException;

class EntityIda
{
    protected int $id;

    public function __construct(int $id)
    {
        $this->ensureIsValidId($id);
        $this->id = $id;
    }

    public function value(): int
    {
        return $this->id;
    }

    private function ensureIsValidId(int $id): void
    {
        if ($id < 0) {
            throw new InvalidArgumentException(
                sprintf('Invalid id:<%d> provided in <%s>', $id, static::class)
            );
        }
    }
}
