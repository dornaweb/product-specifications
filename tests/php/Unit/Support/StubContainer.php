<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Support;

use Psr\Container\ContainerInterface;
use RuntimeException;

final class StubContainer implements ContainerInterface
{
    /**
     * @param array<string, callable(ContainerInterface): mixed> $factories
     */
    public function __construct(private readonly array $factories)
    {
    }

    public function get(string $id): mixed
    {
        if (!isset($this->factories[$id])) {
            throw new RuntimeException(sprintf('No factory registered for %s', $id));
        }

        return ($this->factories[$id])($this);
    }

    public function has(string $id): bool
    {
        return isset($this->factories[$id]);
    }
}
