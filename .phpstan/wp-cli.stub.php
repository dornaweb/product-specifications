<?php

class WP_CLI
{
    public static function add_command(string $name, object|callable|string $callable): bool
    {
    }

    public static function log(string $message): void
    {
    }

    public static function warning(string $message): void
    {
    }

    public static function success(string $message): void
    {
    }

    /**
     * @return never
     */
    public static function error(string $message): void
    {
    }
}
