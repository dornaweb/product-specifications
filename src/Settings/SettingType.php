<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Settings;

enum SettingType: string
{
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case NUMBER = 'number';
    case BOOLEAN = 'toggle';
    case SELECT = 'select';
    case RADIO = 'radio';
    case STRING_LIST = 'checkbox_group';
    case COLOR = 'color';
    case MEDIA = 'media';
}
