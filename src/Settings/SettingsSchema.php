<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Settings;

use LogicException;
use ReflectionClass;

final class SettingsSchema
{
    /** @var list<SettingField>|null */
    private ?array $fields = null;

    /**
     * @param array<string, mixed> $stored
     */
    public function settings(array $stored): Settings
    {
        [$effective] = $this->normalizeStored($stored);

        return $this->hydrate($effective);
    }

    /**
     * @param array<string, mixed> $stored
     */
    public function snapshot(array $stored): SettingsSnapshot
    {
        [$effective, $display, $errors] = $this->normalizeStored($stored);

        return new SettingsSnapshot(
            $this->hydrate($effective),
            $display,
            $errors,
            array_map(
                static fn (SettingField $field): array => $field->toRestSchema(),
                $this->fields()
            ),
            $this->sections()
        );
    }

    /**
     * @param array<string, mixed> $stored
     * @return array{
     *     array<string, string|bool|int|float|list<string>>,
     *     array<string, mixed>,
     *     array<string, string>
     * }
     */
    private function normalizeStored(array $stored): array
    {
        $effective = [];
        $display = [];
        $errors = [];

        foreach ($this->fields() as $field) {
            $value = array_key_exists($field->key, $stored)
                ? $stored[$field->key]
                : $field->default;
            $result = $field->sanitize($value);

            if ($result->valid && $result->value !== null) {
                $effective[$field->key] = $result->value;
                $display[$field->key] = $result->value;
                continue;
            }

            $effective[$field->key] = $field->default;
            $display[$field->key] = is_string($value) || is_bool($value) || is_numeric($value)
                ? $value
                : $field->default;
            $errors[$field->key] = $result->error
                ?? __('This stored value is no longer available.', 'product-specifications');
        }

        return [$effective, $display, $errors];
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, string|bool|int|float|list<string>>
     */
    public function sanitizeForSave(array $input): array
    {
        $allowed = array_map(
            static fn (SettingField $field): string => $field->key,
            $this->fields()
        );
        $unknown = array_diff(array_keys($input), $allowed);
        $errors = [];

        if ($unknown !== []) {
            $errors['settings'] = __('The request contains unknown settings.', 'product-specifications');
        }

        $sanitized = [];
        foreach ($this->fields() as $field) {
            if (!array_key_exists($field->key, $input)) {
                $errors[$field->key] = sprintf(
                    /* translators: %s: setting label. */
                    __('A value is required for %s.', 'product-specifications'),
                    $field->label
                );
                continue;
            }

            $result = $field->sanitize($input[$field->key]);
            if (!$result->valid || $result->value === null) {
                $errors[$field->key] = $result->error
                    ?? __('This value is invalid.', 'product-specifications');
                continue;
            }

            $sanitized[$field->key] = $result->value;
        }

        if ($errors !== []) {
            throw new SettingsValidationException($errors);
        }

        return $sanitized;
    }

    /**
     * @return list<SettingField>
     */
    private function fields(): array
    {
        if ($this->fields !== null) {
            return $this->fields;
        }

        $constructor = (new ReflectionClass(Settings::class))->getConstructor();
        if ($constructor === null) {
            throw new LogicException('The settings object must have a constructor.');
        }

        $fields = [];
        foreach ($constructor->getParameters() as $parameter) {
            $attributes = $parameter->getAttributes(SettingField::class);
            if (count($attributes) !== 1) {
                throw new LogicException('Every settings constructor parameter must declare one setting field.');
            }

            $fields[] = $attributes[0]->newInstance();
        }

        $this->fields = $fields;

        return $this->fields;
    }

    /**
     * @param array<string, string|bool|int|float|list<string>> $values
     */
    private function hydrate(array $values): Settings
    {
        $tabTitle = $values['tab_title'] ?? null;
        $defaultSpecifications = $values['wc_default_specs'] ?? null;
        $loadDefaultStyles = $values['load_default_styles'] ?? null;

        if (!is_string($tabTitle) || !is_string($defaultSpecifications) || !is_bool($loadDefaultStyles)) {
            throw new LogicException('The settings schema does not match the settings object.');
        }

        return new Settings($tabTitle, $defaultSpecifications, $loadDefaultStyles);
    }

    /**
     * @return list<array<string, string>>
     */
    private function sections(): array
    {
        $used = [];
        foreach ($this->fields() as $field) {
            $used[$field->section->value] = true;
        }

        $sections = [
            SettingSection::DISPLAY->value => [
                'key' => SettingSection::DISPLAY->value,
                'title' => __('Display', 'product-specifications'),
                'description' => __(
                    'How specifications appear on the storefront and the product page.',
                    'product-specifications'
                ),
            ],
            SettingSection::INTEGRATION->value => [
                'key' => SettingSection::INTEGRATION->value,
                'title' => __('Integration', 'product-specifications'),
                'description' => __('How the plugin sits inside WooCommerce and your theme.', 'product-specifications'),
            ],
            SettingSection::ADVANCED->value => [
                'key' => SettingSection::ADVANCED->value,
                'title' => __('Advanced', 'product-specifications'),
                'description' => __('Developer-facing and destructive settings.', 'product-specifications'),
            ],
        ];

        return array_values(array_filter(
            $sections,
            static fn (array $section): bool => isset($used[$section['key']])
        ));
    }
}
