<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification;

final class SpecificationDefinition
{
    private function __construct(
        private readonly ?int $id,
        private readonly string $name,
        private readonly string $key,
        private readonly string $description,
        private readonly string $typeId,
        private readonly SpecificationOptions $options,
        private readonly mixed $defaultValue
    ) {
    }

    public static function create(
        string $name,
        string $key,
        string $description,
        string $typeId,
        SpecificationOptions $options,
        mixed $defaultValue
    ): self {
        return new self(null, $name, $key, $description, $typeId, $options, $defaultValue);
    }

    public static function fromStorage(
        int $id,
        string $name,
        string $key,
        string $description,
        string $typeId,
        SpecificationOptions $options,
        mixed $defaultValue
    ): self {
        return new self($id, $name, $key, $description, $typeId, $options, $defaultValue);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function typeId(): string
    {
        return $this->typeId;
    }

    public function options(): SpecificationOptions
    {
        return $this->options;
    }

    public function defaultValue(): mixed
    {
        return $this->defaultValue;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function withDefinition(string $typeId, array $options, mixed $defaultValue): self
    {
        return new self(
            $this->id,
            $this->name,
            $this->key,
            $this->description,
            $typeId,
            SpecificationOptions::fromMap($options),
            $defaultValue
        );
    }
}
