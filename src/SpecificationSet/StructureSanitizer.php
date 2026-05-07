<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet;

final class StructureSanitizer
{
    /**
     * @param mixed $value
     */
    public function sanitize($value): array
    {
        if (!is_array($value)) {
            return StructureMetaDefinition::default();
        }

        return [
            'schema_version' => 1,
            'groups' => $this->sanitizeGroups($value['groups'] ?? []),
        ];
    }

    /**
     * @param mixed $groups
     */
    private function sanitizeGroups($groups): array
    {
        if (!is_array($groups)) {
            return [];
        }

        $sanitized = [];

        foreach ($groups as $group) {
            if (!is_array($group)) {
                continue;
            }

            $key = sanitize_key((string) ($group['key'] ?? ''));

            $sanitized[] = [
                'key' => $key,
                'label' => sanitize_text_field((string) ($group['label'] ?? '')),
                'description' => sanitize_text_field((string) ($group['description'] ?? '')),
                'visible' => (bool) ($group['visible'] ?? true),
                'items' => $this->sanitizeItems($group['items'] ?? []),
            ];
        }

        return $sanitized;
    }

    /**
     * @param mixed $items
     */
    private function sanitizeItems($items): array
    {
        if (!is_array($items)) {
            return [];
        }

        $sanitized = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            if (($item['type'] ?? '') !== 'specification') {
                continue;
            }

            $termId = absint($item['term_id'] ?? 0);
            $key = sanitize_key((string) ($item['key'] ?? ''));

            if ($termId < 1 || $key === '') {
                continue;
            }

            $sanitized[] = [
                'type'    => 'specification',
                'term_id' => $termId,
                'key'     => $key,
            ];
        }

        return $sanitized;
    }
}
