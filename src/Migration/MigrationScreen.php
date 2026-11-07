<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration;

final class MigrationScreen
{
    public const PATH = '/dwps-product-specifications/migration';
    private const ADMIN_PAGE = 'wc-admin';

    public function url(): string
    {
        return add_query_arg(
            [
                'page' => self::ADMIN_PAGE,
                'path' => self::PATH,
            ],
            admin_url('admin.php')
        );
    }

    public function isCurrent(): bool
    {
        return filter_input(INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS) === self::ADMIN_PAGE
            && filter_input(INPUT_GET, 'path', FILTER_SANITIZE_SPECIAL_CHARS) === self::PATH;
    }
}
