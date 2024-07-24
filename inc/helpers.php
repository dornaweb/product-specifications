<?php

declare(strict_types=1);

if (!function_exists('dwspecs_current_page_url')) {
    function dwspecs_current_page_url()
    {

        $pageURL = 'http';
        if (isset($_SERVER["HTTPS"])) {
            if ($_SERVER["HTTPS"] === "on") {
                $pageURL .= "s";
            }
        }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] !== "80") {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }
}

/**
 * Get the list of attributes related to a group
 *
 * @param int $group_id id of the group
 * @return Array
 */
if (!function_exists('dwspecs_get_attributes_by_group')) {
    function dwspecs_get_attributes_by_group($group_id = false)
    {

        if (!$group_id) {
            return;
        }

        return get_terms([
            'taxonomy' => 'spec-attr',
            'hide_empty' => false,
            'meta_key' => 'attr_group',
            'meta_value' => $group_id,
        ]);
    }
}

/**
 * Get attribute value by id|slug|name
 *
 * @param  string $field ( 'id'|'slug'|'name' )
 * @param  mixed  $value id or slug or name
 * @return mixed
*/
if (!function_exists('dwspecs_attr_value_by')) {
    function dwspecs_attr_value_by($post_id = '', $field, $value)
    {

        if (!$post_id) {
            global $post;
            $post_id = $post->ID;
        }

        if (!$post_id) {
            return;
        }

        $table = dwspecs_get_table_result($post_id); // The large array

        if (!is_array($table)) {
            return false;
        }

        foreach ($table as $groupKey => $group) {
            if (isset($group['attributes'])) {
                foreach ($group['attributes'] as $attr) {
                    if ($field === 'id' && $attr['attr_id'] === $value) {
                        return $attr;
                    } elseif ($field === 'slug' && $attr['attr_slug'] === rawurlencode($value)) {
                        return $attr;
                    } elseif ($field === 'name' && $attr['attr_name'] === $value) {
                        return $attr;
                    }
                }
            }
        }

        return null;
    }
}

if (!function_exists('dwspecs_spec_group_has_duplicates')) {
    function dwspecs_spec_group_has_duplicates($name, $tax = 'spec-group')
    {

        if (!$name) {
            return false;
        }

        $terms = get_terms([
            'taxonomy' => $tax,
            'hide_empty' => false,
            'name' => $name,
        ]);

        return count($terms) > 1;
    }
}

/**
 * Get Table Results
 *
 * @param  int     $post_id
 * @return string  $output ( 'serialized'|'array'|'json' )
 * @return mixed   Table result
 */
if (!function_exists('dwspecs_get_table_result')) {
    function dwspecs_get_table_result($post_id = '', $output = 'array', $hide_empty = true)
    {

        if (!$post_id) {
            global $post;
            $post_id = $post->ID;
        }

        if (!$post_id) {
            return;
        }

        $table = get_post_meta($post_id, '_dwps_specification_table', true);

        if (!$table || empty($table)) {
            return;
        }

        // unserialize
        //$result = unserialize( $table );
        $result = $table;

        if ($hide_empty) {
            $result = array_filter($result, static function ($v) {
                return sizeof($v['attributes']) > 0;
            });
        }

        if ($output === 'serialized') {
            $result = serialize($result);
        } elseif ($output === 'json') {
            $result = json_encode($result, JSON_PRETTY_PRINT);
        }

        return $result;
    }
}

/**
 * Get list of groups of a table
 *
 * @param string $format  array|JSON
 * @param int $table_id
 * @return mixed
*/
if (!function_exists('dwspecs_get_table_groups')) {
    function dwspecs_get_table_groups($format = 'array', $table_id = false)
    {

        $output = [];

        if (!$table_id) {
            $tables = new WP_Query([
                'post_type' => 'specs-table',
                'showposts' => -1,
            ]);
            $tbl_array = $tables->get_posts();

            foreach ($tbl_array as $table) {
                $groups = get_post_meta($table->ID, '_groups', true) === '' ? [] : get_post_meta($table->ID, '_groups', true);
                $groups_array = [];

                foreach ($groups as $group) {
                    $group = get_term_by('id', $group, 'spec-group');
                    $groups_array[] = [
                        'name' => $group->name,
                        'term_id' => $group->term_id,
                        'slug' => $group->slug,
                    ];
                }

                $output[] = [
                    'table_id' => $table->ID,
                    'groups' => $groups_array,
                ];
            }
        } elseif (absint($table_id) !== 0) {
            $groups = get_post_meta($table_id, '_groups', true) === '' ? [] : get_post_meta($table_id, '_groups', true);
            $groups_array = [];

            foreach ($groups as $group) {
                $group = get_term_by('id', $group, 'spec-group');
                $groups_array[] = [
                    'name' => $group->name,
                    'term_id' => $group->term_id,
                    'slug' => $group->slug,
                ];
            }

            $output[] = [
                'table_id' => $table_id,
                'groups' => $groups_array,
            ];
        }

        if ($format === 'json') {
            return json_encode($output);
        } else {
            return $output;
        }
    }
}
