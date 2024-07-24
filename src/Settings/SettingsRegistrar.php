<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Settings;

final class SettingsRegistrar
{
    public function register(): void
    {
        register_setting(
            'dwps_options',
            'dwps_options'
        );

        register_setting(
            'dwps_options',
            'dwps_tab_title',
            [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ]
        );

        register_setting(
            'dwps_options',
            'dwps_view_per_page',
            [
                'type' => 'number',
                'sanitize_callback' => 'intval',
                'default' => 15,
            ]
        );

        register_setting(
            'dwps_options',
            'dwps_wc_default_specs',
            [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => 'remove_if_specs_not_empty',
            ]
        );

        register_setting(
            'dwps_options',
            'dwps_disable_default_styles',
            [
                'type' => 'boolean',
                'sanitize_callback' => static fn ($value) => $value === 'on' ? 'on' : null,
            ]
        );
    }
}
