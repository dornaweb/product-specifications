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

        if (! self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Dornaweb_Pack constructor.
     */
    private function __construct()
    {
        // Sync group attributes when an attribute gets updated.
        add_action('dwspecs_attributes_modified', [ $this, 'modified_attributes' ]);

        $this->define_constants();
        $this->init();
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

        if (! defined($name)) {
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

    /**
     * Do initial stuff
     */
    public function init()
    {
        Admin\Admin::init();
    }

    /**
     * Trigger When an attribute is added or deleted and update group attribute ids
     *
     * @param   Array $args
     * @return Array
    */
    public function modified_attributes($args)
    {
        // Sync. delete action with attribute groups
        if ($args['action'] === 'delete') {
            for ($b = 0; $b < sizeof($args['ids']); $b++) {
                $attr_id = $args['ids'][$b];
                $group_id = get_term_meta($attr_id, 'attr_group', true);
                $group_attributes = get_term_meta($group_id, 'attributes', true);

                if (is_array($group_attributes) && sizeof($group_attributes) > 0) {
                    if (in_array($attr_id, $group_attributes)) {
                        unset($group_attributes[ $attr_id ]);
                        $updated_attributes = array_diff($group_attributes, [$attr_id]);
                        update_term_meta($group_id, 'attributes', $updated_attributes);
                    }
                } else {
                    delete_term_meta($group_id, 'attributes');
                    add_term_meta($group_id, 'attributes', []);
                }
            }
        } elseif ($args['action'] === 'add' || $args['action'] === 'edit') {
            for ($i = 0; $i < sizeof($args['ids']); $i++) {
                $attr_id = $args['ids'][$i];
                $group_id = get_term_meta($attr_id, 'attr_group', true);
                $group_attributes = get_term_meta($group_id, 'attributes', true);

                if (is_array($group_attributes) && sizeof($group_attributes) > 0) {
                    if (!in_array($attr_id, $group_attributes)) {
                        array_push($group_attributes, $attr_id);
                        update_term_meta($group_id, 'attributes', $group_attributes);
                    }
                } else {
                    delete_term_meta($group_id, 'attributes');
                    add_term_meta($group_id, 'attributes', [ $attr_id ]);
                }
            }
        }
    }
}
