<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Admin;

final class MajorUpdateWarning
{
    /**
     * Compared against "2.0.0-dev" because PHP ranks 2.0.0 pre-releases such as
     * "2.0.0-beta.1" below "2.0.0".
     */
    private const FIRST_MAJOR_VERSION = '2.0.0-dev';

    /**
     * @param array<string, mixed> $pluginData
     */
    public function print(array $pluginData, object $response): void
    {
        $newVersion = $response->new_version ?? null;

        if (!is_string($newVersion) || version_compare($newVersion, self::FIRST_MAJOR_VERSION, '<')) {
            return;
        }

        printf(
            '<br><strong>%s</strong>',
            esc_html__(
                'Version 2.0.0 changes how specifications are stored. After updating, specification tables disappear from your store until you run the migration. Take a backup first.',
                'product-specifications'
            )
        );
    }
}
