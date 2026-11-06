<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Settings;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final class SettingField
{
    /**
     * @param string|bool|int|float|list<string> $default
     * @param array<string, string> $options
     */
    public function __construct(
        public readonly string $key,
        public readonly SettingType $type,
        public readonly SettingSection $section,
        public readonly string $label,
        public readonly string $description,
        public readonly string|bool|int|float|array $default,
        public readonly array $options = [],
        public readonly string $controlLabel = '',
        public readonly bool $developerOnly = false,
        public readonly bool $destructive = false
    ) {
    }

    public function sanitize(mixed $value): SettingValueResult
    {
        return match ($this->type) {
            SettingType::TEXT => $this->sanitizeText($value, false),
            SettingType::TEXTAREA => $this->sanitizeText($value, true),
            SettingType::NUMBER => $this->sanitizeNumber($value),
            SettingType::BOOLEAN => is_bool($value)
                ? SettingValueResult::valid($value)
                : $this->wrongType(),
            SettingType::SELECT, SettingType::RADIO => $this->sanitizeChoice($value),
            SettingType::STRING_LIST => $this->sanitizeStringList($value),
            SettingType::COLOR => $this->sanitizeColor($value),
            SettingType::MEDIA => $this->sanitizeMedia($value),
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function toRestSchema(): array
    {
        $options = [];
        foreach ($this->options as $value => $label) {
            $options[] = [
                'value' => $value,
                // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
                'label' => translate($label, 'product-specifications'),
            ];
        }

        return [
            'key' => $this->key,
            'type' => $this->type->value,
            'section' => $this->section->value,
            // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
            'label' => translate($this->label, 'product-specifications'),
            // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
            'description' => translate($this->description, 'product-specifications'),
            // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
            'controlLabel' => $this->controlLabel !== ''
                ? translate($this->controlLabel, 'product-specifications')
                : '',
            'default' => $this->default,
            'options' => $options,
            'developerOnly' => $this->developerOnly,
            'destructive' => $this->destructive,
        ];
    }

    private function sanitizeText(mixed $value, bool $multiline): SettingValueResult
    {
        if (!is_string($value)) {
            return $this->wrongType();
        }

        return SettingValueResult::valid(
            $multiline ? sanitize_textarea_field($value) : sanitize_text_field($value)
        );
    }

    private function sanitizeNumber(mixed $value): SettingValueResult
    {
        if (!is_int($value) && !is_float($value)) {
            return $this->wrongType();
        }

        return SettingValueResult::valid($value);
    }

    private function sanitizeChoice(mixed $value): SettingValueResult
    {
        if (!is_string($value) || !array_key_exists($value, $this->options)) {
            return SettingValueResult::invalid(
                sprintf(
                    /* translators: %s: setting label. */
                    __('Choose an available value for %s.', 'product-specifications'),
                    $this->label
                )
            );
        }

        return SettingValueResult::valid($value);
    }

    private function sanitizeStringList(mixed $value): SettingValueResult
    {
        if (!is_array($value) || !array_is_list($value)) {
            return $this->wrongType();
        }

        $sanitized = [];
        foreach ($value as $item) {
            if (!is_string($item)) {
                return $this->wrongType();
            }

            $item = sanitize_text_field($item);
            if ($this->options !== [] && !array_key_exists($item, $this->options)) {
                return SettingValueResult::invalid(
                    sprintf(
                        /* translators: %s: setting label. */
                        __('Choose only available values for %s.', 'product-specifications'),
                        $this->label
                    )
                );
            }

            $sanitized[] = $item;
        }

        return SettingValueResult::valid(array_values(array_unique($sanitized)));
    }

    private function sanitizeColor(mixed $value): SettingValueResult
    {
        if (!is_string($value)) {
            return $this->wrongType();
        }

        $color = sanitize_hex_color($value);

        return is_string($color)
            ? SettingValueResult::valid($color)
            : SettingValueResult::invalid(
                sprintf(
                    /* translators: %s: setting label. */
                    __('Enter a valid hexadecimal color for %s.', 'product-specifications'),
                    $this->label
                )
            );
    }

    private function sanitizeMedia(mixed $value): SettingValueResult
    {
        if (!is_int($value) || $value < 0) {
            return $this->wrongType();
        }

        return SettingValueResult::valid($value);
    }

    private function wrongType(): SettingValueResult
    {
        return SettingValueResult::invalid(
            sprintf(
                /* translators: %s: setting label. */
                __('The value for %s has the wrong type.', 'product-specifications'),
                $this->label
            )
        );
    }
}
