<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Settings;

final class SettingsRepository
{
    public const OPTION_NAME = 'dwps_settings';

    private ?SettingsSnapshot $snapshot = null;
    private ?Settings $settings = null;

    public function __construct(private readonly SettingsSchema $schema)
    {
    }

    public function current(): Settings
    {
        if ($this->settings === null) {
            $this->settings = $this->schema->settings($this->storedValues());
        }

        return $this->settings;
    }

    public function snapshot(): SettingsSnapshot
    {
        if ($this->snapshot === null) {
            $this->snapshot = $this->schema->snapshot($this->storedValues());
            $this->settings = $this->snapshot->settings;
        }

        return $this->snapshot;
    }

    /**
     * @param array<string, mixed> $values
     */
    public function save(array $values): SettingsSnapshot
    {
        $sanitized = $this->schema->sanitizeForSave($values);
        update_option(self::OPTION_NAME, $sanitized, true);
        $this->snapshot = $this->schema->snapshot($sanitized);
        $this->settings = $this->snapshot->settings;

        return $this->snapshot;
    }

    /**
     * @return array<string, mixed>
     */
    private function storedValues(): array
    {
        $stored = get_option(self::OPTION_NAME, null);
        if (is_array($stored)) {
            /** @var array<string, mixed> $stored */
            return $stored;
        }

        $values = [];
        $tabTitle = get_option('dwps_tab_title', null);
        if (is_string($tabTitle)) {
            $values['tab_title'] = $tabTitle;
        }

        $defaultSpecifications = get_option('dwps_wc_default_specs', null);
        if (is_string($defaultSpecifications)) {
            $values['wc_default_specs'] = $defaultSpecifications;
        }

        $disableDefaultStyles = get_option('dwps_disable_default_styles', null);
        if ($disableDefaultStyles !== null) {
            $values['load_default_styles'] = $disableDefaultStyles !== 'on';
        }

        return $values;
    }
}
