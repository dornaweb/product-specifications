<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet;

/**
 * Normalization, not validation. StructureMetaDefinition decides what is
 * acceptable; this fills the defaults WordPress cannot apply to nested object
 * properties, coerces types, and puts keys in the order the mapper writes them.
 *
 * Entries with nothing usable in them are still dropped, because a direct
 * update_post_meta() call never passes through the REST schema and this is the
 * last thing between it and the database.
 */
final class StructureSanitizer
{
    /**
     * @return array<string, mixed>
     */
    public function sanitize(mixed $value): array
    {
        if (!is_array($value)) {
            return StructureMetaDefinition::default();
        }

        return [
            'schema_version' => StructureMetaDefinition::CURRENT_VERSION,
            'groups' => $this->sanitizeGroups($value['groups'] ?? []),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function sanitizeGroups(mixed $groups): array
    {
        if (!is_array($groups)) {
            return [];
        }

        $sanitized = [];

        foreach ($groups as $group) {
            if (!is_array($group)) {
                continue;
            }

            $key = sanitize_key($this->stringValue($group['key'] ?? null));
            $label = sanitize_text_field($this->stringValue($group['label'] ?? null));

            if ($key === '' || $label === '') {
                continue;
            }

            $sanitized[] = [
                'key' => $key,
                'label' => $label,
                'description' => sanitize_text_field($this->stringValue($group['description'] ?? null)),
                'visible' => $this->boolValue($group['visible'] ?? null, true),
                'items' => $this->sanitizeItems($group['items'] ?? []),
            ];
        }

        return $sanitized;
    }

    /**
     * @return list<array<string, bool|int|string>>
     */
    private function sanitizeItems(mixed $items): array
    {
        if (!is_array($items)) {
            return [];
        }

        $sanitized = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $termId = $this->intValue($item['term_id'] ?? null);
            $slug = $this->slugValue($item['slug'] ?? null);

            if ($termId < 1 || $slug === '') {
                continue;
            }

            $sanitized[] = [
                'term_id' => $termId,
                'slug' => $slug,
                'visible' => $this->boolValue($item['visible'] ?? null, true),
            ];
        }

        return $sanitized;
    }

    private function boolValue(mixed $value, bool $default): bool
    {
        return $value === null ? $default : (bool) $value;
    }

    /**
     * A slug for a non-Latin name is stored percent-encoded, so `%` is part of it.
     */
    private function slugValue(mixed $value): string
    {
        return (string) preg_replace('/[^a-z0-9_%-]/', '', strtolower($this->stringValue($value)));
    }

    private function stringValue(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    private function intValue(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        return is_numeric($value) ? absint($value) : 0;
    }
}
