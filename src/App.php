<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs;

final class App
{
    /**
     * Plugin instance.
     *
     * @since 0.1
     * @var null|Amiut\ProductSpecs
     */
    public static $instance = null;

    /**
     * Return the plugin instance.
     *
     * @return Amiut\ProductSpecs\App
     */
    public static function instance()
    {

        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Dornaweb_Pack constructor.
     */
    private function __construct()
    {
        $this->define_constants();
        $this->includes();
    }

    /**
     * Include required files
     *
     */
    public function includes()
    {

        require_once(DWSPECS_ABSPATH . 'inc/helpers.php');
    }

    /**
     * Define constant if not already set.
     *
     * @param string      $name  Constant name.
     * @param string|bool $value Constant value.
     */
    private function define($name, $value)
    {

        if (!defined($name)) {
            define($name, $value);
        }
    }

    /**
     * Define constants
     */
    public function define_constants()
    {

        $this->define('DWSPECS_ABSPATH', dirname(DWSPECS_PLUGIN_FILE) . '/');
    }
}
