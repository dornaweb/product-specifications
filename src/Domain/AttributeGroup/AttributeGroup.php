<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Attribute;

class AttributeGroup
{
    private int $id;
    private ?string $name;
    // private string|int $slug;
    private string $description;

    public function __construct(
        int $id,
        string $name,
        string $slug,
        string $description,
        private string $arr,
    ) {

        $this->id = $id;
        $this->name = $name;
        // $this->slug = $slug;
        $this->description = $description;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    // public function slug(): string
    // {
    //     return $this->slug;
    // }

    public function description(): string
    {
        return $this->description;
    }
}
