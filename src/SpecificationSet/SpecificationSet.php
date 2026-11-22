<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet;

final class SpecificationSet
{
    private function __construct(
        private readonly ?int $id,
        private readonly string $title,
        private readonly string $status,
        private readonly SpecificationSetStructure $structure,
        private readonly string $slug,
        private readonly string $description,
        private readonly SpecificationSetPresentation $presentation
    ) {
    }

    public static function create(
        string $title,
        string $status,
        SpecificationSetStructure $structure,
        string $slug = '',
        string $description = '',
        ?SpecificationSetPresentation $presentation = null
    ): self {
        return new self(
            null,
            $title,
            $status,
            $structure,
            $slug,
            $description,
            $presentation ?? SpecificationSetPresentation::forNewSet()
        );
    }

    public static function fromStorage(
        int $id,
        string $title,
        string $status,
        SpecificationSetStructure $structure,
        string $slug = '',
        string $description = '',
        ?SpecificationSetPresentation $presentation = null
    ): self {
        return new self(
            $id,
            $title,
            $status,
            $structure,
            $slug,
            $description,
            $presentation ?? SpecificationSetPresentation::fromStored(null)
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function isPublished(): bool
    {
        return $this->status === 'publish';
    }

    public function structure(): SpecificationSetStructure
    {
        return $this->structure;
    }

    public function presentation(): SpecificationSetPresentation
    {
        return $this->presentation;
    }

    public function offersComparison(): bool
    {
        return $this->presentation->offersComparison();
    }

    public function withStructure(SpecificationSetStructure $structure): self
    {
        return new self(
            $this->id,
            $this->title,
            $this->status,
            $structure,
            $this->slug,
            $this->description,
            $this->presentation
        );
    }
}
