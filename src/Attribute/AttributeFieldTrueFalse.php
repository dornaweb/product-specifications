<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Attribute;

class AttributeFieldTrueFalse implements AttributeField
{
    public const OPTION_TRUE = 'yes';
    public const OPTION_FALSE = 'no';

    private string $name;
    private string $slug;
    private int $id;
    private string $description;
    private bool $default;
    private ?bool $value;

    public function __construct(
        string $name,
        string $slug,
        int $id,
        bool $default = false,
        string $description = '',
        ?bool $value = null
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

    public function default(): bool
    {
        return $this->default;
    }

    public function value(): ?bool
    {
        return $this->value;
    }

    public function templatePath(): string
    {
        return 'attribute-field-controls/true-false';
    }
}
