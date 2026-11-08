<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Support;

// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- wpdb owns these method names.
final class FakeWpdb
{
    public string $posts = 'wp_posts';
    public string $postmeta = 'wp_postmeta';

    /** @var list<mixed> */
    public array $preparedArguments = [];

    /**
     * @param list<string> $column
     */
    private function __construct(private readonly array $column)
    {
    }

    /**
     * @param list<string> $column
     */
    public static function install(array $column = []): self
    {
        $wpdb = new self($column);
        // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- The unit suite has no database.
        $GLOBALS['wpdb'] = $wpdb;

        return $wpdb;
    }

    public static function uninstall(): void
    {
        unset($GLOBALS['wpdb']);
    }

    public function prepare(string $query, mixed ...$arguments): string
    {
        $this->preparedArguments = $arguments;

        return $query;
    }

    public function esc_like(string $text): string
    {
        return addcslashes($text, '_%\\');
    }

    /**
     * @return list<string>
     */
    public function get_col(string $query): array
    {
        return $this->column;
    }
}
