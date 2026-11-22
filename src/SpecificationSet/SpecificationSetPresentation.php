<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet;

final class SpecificationSetPresentation
{
    private const EMPTINESS_CHOICES = ['hide', 'show'];

    private function __construct(
        private readonly string $emptyRows,
        private readonly string $emptyGroups,
        private readonly bool $comparison
    ) {
    }

    public static function forNewSet(): self
    {
        return new self('hide', 'hide', true);
    }

    /**
     * A set with no stored document, or a malformed one, reads as the defaults, which keep
     * comparison off.
     */
    public static function fromStored(mixed $value): self
    {
        $value = is_array($value) ? $value : [];

        return new self(
            self::emptiness($value['empty_rows'] ?? null),
            self::emptiness($value['empty_groups'] ?? null),
            ($value['comparison'] ?? false) === true
        );
    }

    public function offersComparison(): bool
    {
        return $this->comparison;
    }

    public function withComparison(bool $comparison): self
    {
        return new self($this->emptyRows, $this->emptyGroups, $comparison);
    }

    /**
     * @return array{schema_version: int, empty_rows: string, empty_groups: string, comparison: bool}
     */
    public function toArray(): array
    {
        return [
            'schema_version' => PresentationMetaDefinition::CURRENT_VERSION,
            'empty_rows' => $this->emptyRows,
            'empty_groups' => $this->emptyGroups,
            'comparison' => $this->comparison,
        ];
    }

    private static function emptiness(mixed $value): string
    {
        return in_array($value, self::EMPTINESS_CHOICES, true) ? $value : 'hide';
    }
}
