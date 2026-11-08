<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration\Soft;

use Amiut\ProductSpecs\Migration\SoftMigration;
use Amiut\ProductSpecs\Settings\SettingsRepository;

final class LegacySettingsMigration implements SoftMigration
{
    public function migrate(): void
    {
        if (is_array(get_option(SettingsRepository::OPTION_NAME, null))) {
            return;
        }

        $values = $this->legacyValues();

        if ($values !== []) {
            update_option(SettingsRepository::OPTION_NAME, $values, true);
        }
    }

    /**
     * @return array<string, string|bool>
     */
    private function legacyValues(): array
    {
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
