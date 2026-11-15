<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet;

final class SpecificationGroup
{
    /**
     * @param list<SpecificationSetItem> $items
     */
    public function __construct(
        private readonly string $key,
        private readonly string $label,
        private readonly string $description,
        private readonly bool $visible,
        private readonly array $items
    ) {
    }

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function visible(): bool
    {
        return $this->visible;
    }

    /**
     * @return list<SpecificationSetItem>
     */
    public function items(): array
    {
        return $this->items;
    }

    public function hasItem(string $slug): bool
    {
        return $this->positionOf($slug) !== null;
    }

    public function item(string $slug): SpecificationSetItem
    {
        $position = $this->positionOf($slug);

        if ($position === null) {
            throw InvalidStructureOperationException::unknownItem($this->key, $slug);
        }

        return $this->items[$position];
    }

    public function withLabel(string $label): self
    {
        return new self(
            $this->key,
            $label,
            $this->description,
            $this->visible,
            $this->items
        );
    }

    public function withDescription(string $description): self
    {
        return new self(
            $this->key,
            $this->label,
            $description,
            $this->visible,
            $this->items
        );
    }

    public function withItem(SpecificationSetItem $item): self
    {
        if ($this->hasItem($item->slug())) {
            throw InvalidStructureOperationException::duplicateItem(
                $this->key,
                $item->slug()
            );
        }

        return $this->withItems([...$this->items, $item]);
    }

    public function withItemAt(SpecificationSetItem $item, int $position): self
    {
        if ($this->hasItem($item->slug())) {
            throw InvalidStructureOperationException::duplicateItem(
                $this->key,
                $item->slug()
            );
        }

        $items = $this->items;
        array_splice($items, $this->guardPosition($position, count($items)), 0, [$item]);

        return $this->withItems($items);
    }

    public function withItemMovedTo(string $slug, int $position): self
    {
        $from = $this->positionOf($slug);

        if ($from === null) {
            throw InvalidStructureOperationException::unknownItem($this->key, $slug);
        }

        $items = $this->items;
        $moved = array_splice($items, $from, 1);
        array_splice($items, $this->guardPosition($position, count($this->items) - 1), 0, $moved);

        return $this->withItems($items);
    }

    public function withoutItem(string $slug): self
    {
        $position = $this->positionOf($slug);

        if ($position === null) {
            throw InvalidStructureOperationException::unknownItem($this->key, $slug);
        }

        $items = $this->items;
        array_splice($items, $position, 1);

        return $this->withItems($items);
    }

    /**
     * @param array<SpecificationSetItem> $items
     */
    private function withItems(array $items): self
    {
        return new self(
            $this->key,
            $this->label,
            $this->description,
            $this->visible,
            array_values($items)
        );
    }

    private function positionOf(string $slug): ?int
    {
        foreach ($this->items as $position => $item) {
            if ($item->slug() === $slug) {
                return $position;
            }
        }

        return null;
    }

    private function guardPosition(int $position, int $lastPosition): int
    {
        if ($position < 0 || $position > $lastPosition) {
            throw InvalidStructureOperationException::positionOutOfRange($position, $lastPosition);
        }

        return $position;
    }
}
