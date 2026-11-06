<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Settings;

final class SettingsSnapshot
{
    /**
     * @param array<string, mixed> $values
     * @param array<string, string> $errors
     * @param list<array<string, mixed>> $fields
     * @param list<array<string, string>> $sections
     */
    public function __construct(
        public readonly Settings $settings,
        public readonly array $values,
        public readonly array $errors,
        public readonly array $fields,
        public readonly array $sections
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toRestResponse(bool $canManage): array
    {
        return [
            'settings' => $this->values,
            'errors' => $this->errors,
            'fields' => $this->fields,
            'sections' => $this->sections,
            'canManage' => $canManage,
        ];
    }
}
