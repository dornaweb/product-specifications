<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration\Step;

use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;

/**
 * The snapshot holds a copy of each specification's slug from the moment the product
 * is saved, and a slug can change afterwards, so the key is always the current slug,
 * looked up by id.
 */
final class SnapshotValueReader
{
    private const NO_SELECTION = 'dwspecs_chk_none';

    public function __construct(
        private readonly SpecificationLookup $lookup,
        private readonly SpecificationTypeRegistry $types
    ) {
    }

    /**
     * @return array{values: array<string, mixed>, unresolved: list<string>}
     */
    public function read(mixed $snapshot): array
    {
        $values = [];
        $unresolved = [];

        foreach ($this->entries($snapshot) as $entry) {
            if ($entry['value'] === '' || $entry['value'] === self::NO_SELECTION) {
                continue;
            }

            $definition = $this->lookup->find($entry['id'])
                ?? $this->lookup->recreate($entry['id'], $entry['name'], $entry['slug'], $entry['description']);

            if ($definition === null) {
                $unresolved[] = $entry['name'] !== '' ? $entry['name'] : $entry['slug'];
                continue;
            }

            $value = $this->types->get($definition->typeId())->sanitize(
                html_entity_decode($entry['value'], ENT_QUOTES | ENT_HTML5, 'UTF-8')
            );

            if ($value !== null) {
                $values[$definition->key()] = $value;
            }
        }

        return ['values' => $values, 'unresolved' => $unresolved];
    }

    /**
     * @return list<array{id: int, name: string, slug: string, description: string, value: string}>
     */
    private function entries(mixed $snapshot): array
    {
        $entries = [];

        foreach (is_array($snapshot) ? $snapshot : [] as $group) {
            $attributes = is_array($group) ? ($group['attributes'] ?? null) : null;

            foreach (is_array($attributes) ? $attributes : [] as $attribute) {
                if (!is_array($attribute)) {
                    continue;
                }

                $entries[] = [
                    'id' => is_numeric($attribute['attr_id'] ?? null) ? (int) $attribute['attr_id'] : 0,
                    'name' => $this->text($attribute['attr_name'] ?? null),
                    'slug' => $this->text($attribute['attr_slug'] ?? null),
                    'description' => $this->text($attribute['attr_desc'] ?? null),
                    'value' => trim($this->text($attribute['value'] ?? null)),
                ];
            }
        }

        return $entries;
    }

    private function text(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
