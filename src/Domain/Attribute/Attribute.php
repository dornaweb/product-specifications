<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Domain\Attribute;

class Attribute
{
    private AttributeId $id;
    private AttributeName $name;
    private AttributeSlug $slug;
    private ?AttributeGroup $group;
    private string $fieldType;
    private string $defaultValue;
    private AttributeDescription $description;

    public function __construct(
        AttributeId $id,
    ) {

        $this->id = $id;
    }
}
