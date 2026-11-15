<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Settings;

enum SettingSection: string
{
    case DISPLAY = 'display';
    case INTEGRATION = 'integration';
    case ADVANCED = 'advanced';
}
