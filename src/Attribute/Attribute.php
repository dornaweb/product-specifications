<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Attribute;

class Attribute
{
    private AttributeId $id;
    private AttributeName $name;
    private AttributeSlug $slug;
    private string $fieldType;
    private string $defaultValue;
    private AttributeDescription $description;
    public const HELLO = '';

    public function __construct(
        AttributeId $id,
        AttributeName $name,
        AttributeSlug $slug,
        string $fieldType,
        string $defaultValue,
        AttributeDescription $description,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->fieldType = $fieldType;
        $this->defaultValue = $defaultValue;
        $this->description = $description;
    }
}
