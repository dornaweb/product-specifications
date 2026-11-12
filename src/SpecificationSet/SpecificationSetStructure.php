<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet;

final class SpecificationSetStructure
{
    /**
     * @var list<SpecificationGroup>
     */
    private readonly array $groups;

    /**
     * @param array<SpecificationGroup> $groups
     */
    public function __construct(private readonly int $schemaVersion, array $groups)
    {
        $this->groups = array_values($groups);
    }

    public static function empty(): self
    {
        return new self(StructureMetaDefinition::CURRENT_VERSION, []);
    }

    public function schemaVersion(): int
    {
        return $this->schemaVersion;
    }

    /**
     * @return list<SpecificationGroup>
     */
    public function groups(): array
    {
        return $this->groups;
    }

    public function hasGroup(string $key): bool
    {
        return $this->positionOf($key) !== null;
    }

    public function group(string $key): SpecificationGroup
    {
        return $this->groups[$this->guardGroup($key)];
    }

    public function withGroup(SpecificationGroup $group): self
    {
        if ($this->hasGroup($group->key())) {
            throw InvalidStructureOperationException::duplicateGroupKey($group->key());
        }

        return $this->withGroups([...$this->groups, $group]);
    }

    public function containsItem(int $termId): bool
    {
        foreach ($this->groups as $group) {
            if ($group->hasItem($termId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Only adds. A group or an item already here keeps its place, and a group in both
     * takes the incoming label and description.
     */
    public function mergedWith(self $incoming): self
    {
        $merged = $this;

        foreach ($incoming->groups() as $group) {
            $merged = $merged->hasGroup($group->key())
                ? $merged->withGroupDetails($group->key(), $group->label(), $group->description())
                : $merged->withGroup(
                    new SpecificationGroup($group->key(), $group->label(), $group->description(), $group->visible(), [])
                );

            foreach ($group->items() as $item) {
                if (!$merged->containsItem($item->termId())) {
                    $merged = $merged->withItem($group->key(), $item);
                }
            }
        }

        return $merged;
    }

    public function withGroupLabel(string $key, string $label): self
    {
        $position = $this->guardGroup($key);
        $groups = $this->groups;
        $groups[$position] = $groups[$position]->withLabel($label);

        return $this->withGroups($groups);
    }

    public function withGroupMovedTo(string $key, int $position): self
    {
        $from = $this->guardGroup($key);
        $groups = $this->groups;
        $moved = array_splice($groups, $from, 1);
        array_splice($groups, $this->guardPosition($position, count($this->groups) - 1), 0, $moved);

        return $this->withGroups($groups);
    }

    public function withoutGroup(string $key): self
    {
        $groups = $this->groups;
        array_splice($groups, $this->guardGroup($key), 1);

        return $this->withGroups($groups);
    }

    public function withItem(string $groupKey, SpecificationSetItem $item): self
    {
        $position = $this->guardGroup($groupKey);
        $groups = $this->groups;
        $groups[$position] = $groups[$position]->withItem($item);

        return $this->withGroups($groups);
    }

    /**
     * Moving inside one group is a reorder; moving between two is a transfer.
     * Both land the item at the given position in the destination list as it
     * exists once the item has left its source.
     */
    public function withItemMovedTo(
        string $fromGroupKey,
        int $termId,
        string $toGroupKey,
        int $position
    ): self {
        $from = $this->guardGroup($fromGroupKey);

        if ($fromGroupKey === $toGroupKey) {
            $groups = $this->groups;
            $groups[$from] = $groups[$from]->withItemMovedTo($termId, $position);

            return $this->withGroups($groups);
        }

        $to = $this->guardGroup($toGroupKey);
        $item = $this->groups[$from]->item($termId);

        $groups = $this->groups;
        $groups[$from] = $groups[$from]->withoutItem($termId);
        $groups[$to] = $groups[$to]->withItemAt($item, $position);

        return $this->withGroups($groups);
    }

    public function withoutItem(string $groupKey, int $termId): self
    {
        $position = $this->guardGroup($groupKey);
        $groups = $this->groups;
        $groups[$position] = $groups[$position]->withoutItem($termId);

        return $this->withGroups($groups);
    }

    public function withoutItemEverywhere(int $termId): self
    {
        $groups = array_map(
            static fn (SpecificationGroup $group): SpecificationGroup => $group->hasItem($termId)
                ? $group->withoutItem($termId)
                : $group,
            $this->groups
        );

        return $this->withGroups($groups);
    }

    private function withGroupDetails(string $key, string $label, string $description): self
    {
        $position = $this->guardGroup($key);
        $groups = $this->groups;
        $groups[$position] = $groups[$position]->withLabel($label)->withDescription($description);

        return $this->withGroups($groups);
    }

    /**
     * @param array<SpecificationGroup> $groups
     */
    private function withGroups(array $groups): self
    {
        return new self($this->schemaVersion, array_values($groups));
    }

    private function positionOf(string $key): ?int
    {
        foreach ($this->groups as $position => $group) {
            if ($group->key() === $key) {
                return $position;
            }
        }

        return null;
    }

    private function guardGroup(string $key): int
    {
        $position = $this->positionOf($key);

        if ($position === null) {
            throw InvalidStructureOperationException::unknownGroup($key);
        }

        return $position;
    }

    private function guardPosition(int $position, int $lastPosition): int
    {
        if ($position < 0 || $position > $lastPosition) {
            throw InvalidStructureOperationException::positionOutOfRange($position, $lastPosition);
        }

        return $position;
    }
}
