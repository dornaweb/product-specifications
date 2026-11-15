<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Settings;

interface SettingsProvider
{
    /**
     * @return list<SettingField>
     */
    public function fields(): array;
}
