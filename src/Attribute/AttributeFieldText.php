<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Attribute;

class AttributeFieldText implements AttributeField
{
    private string $name;
    private string $slug;
    private int $id;
    private string $description;
    private string $default;
    private ?string $value;

    public function __construct(
        string $name,
        string $slug,
        int $id,
        string $default = '',
        string $description = '',
        ?string $value = null
    ) {

        $this->name = $name;
        $this->slug = $slug;
        $this->id = $id;
        $this->default = $default;
        $this->description = $description;
        $this->value = $value;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function default(): string
    {
        return $this->default;
    }

    public function value(): ?string
    {
        return $this->value;
    }

    public function templatePath(): string
    {
        return 'attribute-field-controls/text';
    }
}
