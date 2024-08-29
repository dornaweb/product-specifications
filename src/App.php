<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs;

final class App
{
    /**
     * Plugin instance.
     *
     * @since 0.1
     * @var ?App
     */
    private static $instance = null;

    /**
     * Return the plugin instance.
     */
    public static function instance(): App
    {

        if (is_null(self::$instance)) {
            self::$instance = new App();
        }

        return self::$instance;
    }

    /**
     * Dornaweb_Pack constructor.
     */
    private function __construct()
    {
        $this->defineConstants();
        $this->includes();
    }

    /**
     * Include required files
     *
     */
    public function includes(): void
    {

        require_once(DWSPECS_ABSPATH . 'inc/helpers.php');
    }

    /**
     * Define constant if not already set.
     *
     * @param string      $name  Constant name.
     * @param string|bool $value Constant value.
     */
    private function define($name, $value): void
    {

        if (!defined($name)) {
            define($name, $value);
        }
    }

    /**
     * Define constants
     */
    public function defineConstants(): void
    {

        $this->define('DWSPECS_ABSPATH', dirname(DWSPECS_PLUGIN_FILE) . '/');
    }
}
