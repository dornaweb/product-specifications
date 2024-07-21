<?php

declare(strict_types=1);

/**
 * Admin Ajax Controllers
 *
 * @author Dornaweb
 * @contribute Am!n <dornaweb.com>
 */

namespace Amiut\ProductSpecs\Admin;

defined('ABSPATH') || exit;

class Ajax
{
    /**
     * Init
    */
    public static function init()
    {
        /* Registering ajax actions with their callbacks */
        add_action('wp_ajax_dwps_modify_attributes', [ __CLASS__, 'modify_attributes' ]);
        add_action('wp_ajax_dwps_edit_form', [ __CLASS__, 'edit_form' ]);
        add_action('wp_ajax_dwps_group_rearange', [ __CLASS__, 'group_re_arange' ]);
    }

    /**
     * Callback for modyfying attributes
     *
     * @return string JSON result
    */
    public static function modify_attributes()
    {

        if (!isset($_POST['do']) || empty($_POST['do'])) {
            $do = 'add';
        } else {
            $do = sanitize_text_field($_POST['do']);
        }

        // Add or edit attrubute
        if ($do === 'add' || $do === 'edit') {
            $attr_name = isset($_POST['attr_name']) ? sanitize_text_field(htmlspecialchars($_POST['attr_name'])) : false;

            $attr_slug = isset($_POST['attr_slug']) ? sanitize_text_field(htmlspecialchars($_POST['attr_slug'])) : '';

            $attr_desc = isset($_POST['attr_desc']) ? sanitize_text_field(htmlspecialchars($_POST['attr_desc'])) : '';

            $attr_group = isset($_POST['attr_group']) ? sanitize_text_field(htmlspecialchars($_POST['attr_group'])) : false;

            $attr_type = isset($_POST['attr_type']) ? sanitize_text_field(htmlspecialchars($_POST['attr_type'])) : false;

            $attr_values = isset($_POST['attr_values']) ? htmlspecialchars($_POST['attr_values']) : false;

            $attr_default = isset($_POST['attr_default']) && !empty($_POST['attr_default']) ? htmlspecialchars($_POST['attr_default']) : false;

            if ($attr_type === 'true_false') {
                $attr_default = $attr_default === 'on' ? 'yes' : 'no';
            }

            $attr_values = $attr_values ? explode("\n", $attr_values) : [];

            // validation
            if (!$attr_name || empty($attr_name)) {
                $result = json_encode([
                    'result' => 'error',
                    'message' => esc_html__('Please enter an attribute name', 'product-specifications'),
                    'action' => $do,
                    'where' => ['input#attr_name'],
                ]);
            } elseif (!$attr_group || empty($attr_group)) {
                $result = json_encode([
                    'result' => 'error',
                    'message' => esc_html__('Please select a group', 'product-specifications'),
                    'action' => $do,
                    'where' => ['input#attr_group'],
                ]);
            } elseif (!$attr_type || empty($attr_type)) {
                $result = json_encode([
                    'result' => 'error',
                    'message' => esc_html__('Please select a field type', 'product-specifications'),
                    'action' => $do,
                    'where' => ['input#attr_type'],
                ]);
            } elseif ($attr_type && ($attr_type === 'select' || $attr_type === 'radio') && (!$attr_values || empty($attr_values))) {
                $result = json_encode([
                    'result' => 'error',
                    'message' => esc_html__('Please set some values for attribute', 'product-specifications'),
                    'action' => $do,
                    'where' => ['input#attr_values'],
                ]);
            }

            // No validation error so far
            else {
                if ($do === 'add') {
                    $check = wp_insert_term($attr_name, 'spec-attr', [
                        'description' => $attr_desc,
                        'slug' => $attr_slug,
                    ]);
                } elseif ($do === 'edit' && isset($_POST['id'])) {
                    $attr_id = $_POST['id'] ? absint($_POST['id']) : false;
                    $check = wp_update_term($attr_id, 'spec-attr', [
                        'name' => $attr_name,
                        'description' => $attr_desc,
                        'slug' => $attr_slug,
                    ]);
                }

                $metas = [
                    'attr_group' => $attr_group,
                    'attr_type' => $attr_type,
                    'attr_values' => array_filter(array_map('trim', $attr_values)),
                    'attr_default' => rawurldecode((string) $attr_default),
                ];

                if ($check && !is_WP_Error($check)) {
                    foreach ($metas as $meta_key => $value) {
                        if (get_term_meta($check['term_id'], $meta_key, true) !== $value && $value) {
                            delete_term_meta($check['term_id'], $meta_key);
                            add_term_meta($check['term_id'], $meta_key, $value);
                        }
                    }

                    $result = json_encode([
                        'result' => 'success',
                        'message' => $do === 'add' ? esc_html__('Attribute added successfully', 'product-specifications') : esc_html__('Attribute updated successfully', 'product-specifications'),
                        'action' => $do,
                    ]);

                    /*
                     * action trigger
                    */
                    $ac_args = [
                        'action' => $do,
                        'ids' => is_array($check['term_id']) ? $check['term_id'] : [ $check['term_id'] ],
                    ];

                    do_action('dwspecs_attributes_modified', $ac_args);
                } else {
                    $result = json_encode([
                        'result' => 'error',
                        'message' => $check->errors["term_exists"][0] ?: esc_html__('Something went wrong', 'product-specifications'),
                        'action' => $do,
                        'where' => 'unknown',
                    ]);
                }
            }

            die($result);
        }

        // Delete attribute( s )
        elseif ($do === 'delete') {
            $attr_id = isset($_POST['id']) ? (is_array($_POST['id']) ? array_filter($_POST['id'], 'intval') : absint($_POST['id'])) : '';
            $check = false;

            /*
             * First, Delete attributes from group meta ( "attributes" ) whether or not the deleting operation is successful.
            */
            $ac_args = [
                'action' => 'delete',
                'ids' => is_array($attr_id) ? $attr_id : [ $attr_id ],
            ];
            do_action('dwspecs_attributes_modified', $ac_args);

            if ($attr_id && is_array($attr_id)) {
                for ($i = 0; $i < sizeof($attr_id); $i++) {
                    $check = wp_delete_term($attr_id[ $i ], 'spec-attr');
                }
            } elseif ($attr_id && $attr_id > 0) {
                $check = wp_delete_term($attr_id, 'spec-attr');
            }

            if (! $check || is_WP_Error($check)) {
                $result = json_encode([
                    'result' => 'error',
                    'message' => esc_html__('Could not delete attribute(s)', 'product-specifications'),
                    'action' => 'delete',
                ]);

                /*
                 * Now if the deleting operation fails, add the attributes back to group meta( "attributes" )
                */
                $ac_args = [
                    'action' => 'add',
                    'ids' => is_array($attr_id) ? $attr_id : [ $attr_id ],
                ];
                do_action('dwspecs_attributes_modified', $ac_args);

                die($result);
            } else {
                $result = json_encode([
                    'result' => 'success',
                    'message' => esc_html__('Attribute(s) deleted successfully', 'product-specifications'),
                    'action' => 'delete',
                ]);

                /*
                 * action trigger
                */
                $ac_args = [
                    'action' => 'delete',
                    'ids' => is_array($attr_id) ? $attr_id : [ $attr_id ],
                ];
                do_action('dwspecs_attributes_modified', $ac_args);

                die($result);
            }
        }

        die(json_encode([
            'result' => 'error',
            'message' => esc_html__('Unexpected error', 'product-specifications'),
            'action' => 'unknown',
        ]));

        exit;
    }

    /**
     * Edit form
     *
     * @return void
    */
    public static function edit_form()
    {

        $id = isset($_REQUEST['id']) ? absint($_REQUEST['id']) : false;
        $type = isset($_REQUEST['type']) ? esc_attr(htmlspecialchars($_REQUEST['type'])) : false;
        if (!$id) {
            die;
        }

        if ($type === 'attribute') {
            Admin::get_template('views/edit-attribute-form', [ 'attribute_id' => $id ]);
        } else {
            Admin::get_template('views/edit-group-form', [ 'group_id' => $id ]);
        }

        wp_die();
    }

    /**
     * Group Re-arrange
     *
     * @return void
    */
    public static function group_re_arange()
    {

        if (isset($_POST) && isset($_POST['group_id']) && !empty($_POST['group_id'])) {
            // update Arrangement
            $group_id = absint($_POST['group_id']);
            $attributes = array_filter($_POST['attr'], 'intval');
            $check = update_term_meta($group_id, 'attributes', $attributes);
            if ($check) {
                die(json_encode([
                    'result' => 'success',
                    'message' => esc_html__('Attributes arrangement successfully updated.', 'product-specifications'),
                ]));
            }
        } else {
            $id = isset($_REQUEST['id']) ? absint($_REQUEST['id']) : false;
            if (!$id) {
                die;
            }
            Admin::get_template('views/group-rearange-form', [ 'group_id' => $id ]);
        }
        wp_die();
    }
}
