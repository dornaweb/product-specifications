<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type;

final class SpecificationTypeRegistry
{
    public const REGISTER_ACTION = 'product_specifications_register_types';

    /**
     * @var array<string, SpecificationType>
     */
    private array $types = [];

    public function __construct(private readonly SpecificationType $fallback)
    {
        $this->register($fallback);
    }

    public function register(SpecificationType $type): void
    {
        $this->types[$type->id()] = $type;
    }

    public function has(string $id): bool
    {
        return isset($this->types[$id]);
    }

    /**
     * A type registered over the fallback's id becomes the fallback.
     */
    public function get(string $id): SpecificationType
    {
        return $this->types[$id] ?? $this->types[$this->fallback->id()] ?? $this->fallback;
    }

    /**
     * @return array<string, SpecificationType>
     */
    public function all(): array
    {
        return $this->types;
    }
}
