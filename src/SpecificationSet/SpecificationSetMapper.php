<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet;

use WP_Post;

final class SpecificationSetMapper
{
    public function fromPost(WP_Post $post): SpecificationSet
    {
        return SpecificationSet::fromStorage(
            $post->ID,
            $post->post_title,
            $post->post_status,
            $this->structureFromArray(get_post_meta($post->ID, StructureMetaDefinition::KEY, true)),
            $post->post_name,
            $post->post_content
        );
    }

    public function structureFromArray(mixed $value): SpecificationSetStructure
    {
        if (!is_array($value)) {
            return SpecificationSetStructure::empty();
        }

        return new SpecificationSetStructure(
            $this->intValue($value['schema_version'] ?? null, StructureMetaDefinition::CURRENT_VERSION),
            $this->groupsFromArray($value['groups'] ?? [])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function structureToArray(SpecificationSetStructure $structure): array
    {
        return [
            'schema_version' => $structure->schemaVersion(),
            'groups' => array_map(
                fn (SpecificationGroup $group): array => $this->groupToArray($group),
                $structure->groups()
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     * @phpstan-return array{ID?: int, post_type: string, post_title: string, post_status: string}
     */
    public function postArguments(SpecificationSet $set): array
    {
        $arguments = [
            'post_type' => SpecificationSetPostType::KEY,
            'post_title' => $set->title(),
            'post_status' => $set->status(),
            'post_content' => $set->description(),
        ];

        if ($set->slug() !== '') {
            $arguments['post_name'] = $set->slug();
        }

        if (is_int($set->id())) {
            $arguments['ID'] = $set->id();
        }

        return $arguments;
    }

    /**
     * @return list<SpecificationGroup>
     */
    private function groupsFromArray(mixed $groups): array
    {
        if (!is_array($groups)) {
            return [];
        }

        $mapped = [];

        foreach ($groups as $group) {
            if (!is_array($group)) {
                continue;
            }

            $mapped[] = new SpecificationGroup(
                $this->stringValue($group['key'] ?? null),
                $this->stringValue($group['label'] ?? null),
                $this->stringValue($group['description'] ?? null),
                (bool) ($group['visible'] ?? true),
                $this->itemsFromArray($group['items'] ?? [])
            );
        }

        return $mapped;
    }

    /**
     * @return list<SpecificationSetItem>
     */
    private function itemsFromArray(mixed $items): array
    {
        if (!is_array($items)) {
            return [];
        }

        $mapped = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $mapped[] = SpecificationSetItem::create(
                $this->intValue($item['term_id'] ?? null),
                $this->stringValue($item['slug'] ?? null),
                (bool) ($item['visible'] ?? true),
                (bool) ($item['comparable'] ?? false)
            );
        }

        return $mapped;
    }

    /**
     * @return array<string, mixed>
     */
    private function groupToArray(SpecificationGroup $group): array
    {
        return [
            'key' => $group->key(),
            'label' => $group->label(),
            'description' => $group->description(),
            'visible' => $group->visible(),
            'items' => array_map(
                fn (SpecificationSetItem $item): array => $this->itemToArray($item),
                $group->items()
            ),
        ];
    }

    /**
     * @return array{term_id: int, slug: string, visible: bool, comparable: bool}
     */
    private function itemToArray(SpecificationSetItem $item): array
    {
        return [
            'term_id' => $item->termId(),
            'slug' => $item->slug(),
            'visible' => $item->visible(),
            'comparable' => $item->comparable(),
        ];
    }

    private function stringValue(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    private function intValue(mixed $value, int $default = 0): int
    {
        if (is_int($value)) {
            return $value;
        }

        return is_numeric($value) ? (int) $value : $default;
    }
}
