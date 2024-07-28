<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport;

final class ImportDataAjaxHandler
{
    /**
     * @wp-hook wp_ajax_dwspecs_import_data
     * @return never
     */
    // phpcs:ignore Inpsyde.CodeQuality.FunctionLength.TooLong
    public function __invoke(): void
    {
        header('Content-type: application/json; charset=utf-8');

        $nonce = (string) filter_input(INPUT_POST, 'dws_im_nonce', FILTER_SANITIZE_STRING);

        if (!(bool) wp_verify_nonce($nonce, 'dwspecs_nonce_import')) {
            wp_send_json_error(
                [
                    'message' => esc_html__(
                        'Invalid request',
                        'product-specifications'
                    ),
                ]
            );
        }

        // Exit if already migrating
        if (false !== get_transient('dwspecs_data_migrating')) {
            wp_send_json_error(
                [
                    'message' => esc_html__(
                        'Another Migration is in progress',
                        'product-specifications'
                    ),
                ]
            );
        }

        $fileInput = $_FILES['file'] ?? null; // phpcs:ignore

        if (is_null($fileInput)) {
            wp_send_json_error(
                [
                    'message' => esc_html__(
                        'Invalid file',
                        'product-specifications'
                    ),
                ]
            );
        }

        if ($fileInput['type'] !== 'application/json' || !$fileInput['size']) {
            wp_send_json_error(
                [
                    'message' => esc_html__('Invalid File', 'product-specifications'),
                ]
            );
        }

        $data = json_decode((string) file_get_contents($fileInput['tmp_name']), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error([
                'message' => esc_html__('Invalid File', 'product-specifications'),
            ]);
        }

        set_transient('dwspecs_data_migrating', true, MINUTE_IN_SECONDS * 30);

        // Migrate Plugin data
        $this->importPluginData((array) $data);

        delete_transient('dwspecs_data_migrating');

        wp_send_json_success([
            'message' => esc_html__('Data Imported successfully', 'product-specifications'),
        ]);

        wp_die();
    }

    // phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
    // phpcs:disable Inpsyde.CodeQuality.NestingLevel.MaxExceeded
    private function importPluginData(array $data): void
    {
        $ids_map = [];
        global $wpdb;

        foreach ($data as $table) {
            // Check for existing table
            $existing_table = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type= %s AND post_status = 'publish'",
                    $table['table-slug'],
                    'specs-table'
                )
            );

            if ($existing_table) {
                $new_table_id = $existing_table;
            } else {
                $new_table_id = wp_insert_post([
                    'post_type' => 'specs-table',
                    'post_title' => $table['table-title'],
                    'post_name' => $table['table-slug'],
                    'post_status' => 'publish',
                    'meta_input' => [
                        'old_id' => $table['table-id'],
                    ],
                ]);
            }

            foreach ($table['skleton'] as $group) {
                /**
                 * Create Attr-group
                 */
                // Check for existing group
                if ($existing_group = get_term_by('slug', $group['slug'], 'spec-group')) {
                    $new_group_id = $existing_group->term_id;
                } else {
                    // Create new
                    $new_group_id = wp_insert_term($group['name'], 'spec-group', [
                        'slug' => $group['slug'],
                    ]);
                    $new_group_id = $new_group_id['term_id'];

                    update_term_meta($new_group_id, 'old_id', $group['term_id']);
                }

                $ids_map[$group['term_id']] = $new_group_id;

                /**
                 * Create Attributes
                 */
                foreach ($group['attributes'] as $attribute) {
                    // Check for existing attribute
                    if ($existing_attribute = get_term_by('slug', $attribute['slug'], 'spec-attr')) {
                        $new_attr_id = $existing_attribute->term_id;
                    } else {
                        // Create new
                        $new_attr_id = wp_insert_term($attribute['title'], 'spec-attr', [
                            'slug' => $attribute['slug'],
                        ]);
                        $new_attr_id = $new_attr_id['term_id'];

                        update_term_meta($new_attr_id, 'old_id', $attribute['id']);
                    }

                    $ids_map[$attribute['id']] = $new_attr_id;

                    update_term_meta($new_attr_id, 'attr_group', $new_group_id);
                    update_term_meta($new_attr_id, 'attr_type', $attribute['type']);
                    update_term_meta($new_attr_id, 'attr_values', $attribute['values']);
                    update_term_meta($new_attr_id, 'attr_default', $attribute['default']);
                }

                // Update Arrangement orders information
                if ($new_group_id && !is_wp_error($new_group_id)) {
                    $new_order = array_map(static function ($element) use ($ids_map) {
                        if (isset($ids_map[$element])) {
                            return str_replace($element, $ids_map[$element], $element);
                        } else {
                            return $element;
                        }
                    }, $group['attributes-order']);
                    update_term_meta($new_group_id, 'attributes', $new_order);
                }
            }

            // Update table groups order
            $table_g_order = array_map(static function ($element) use ($ids_map) {
                if (isset($ids_map[$element])) {
                    return str_replace($element, $ids_map[$element], $element);
                } else {
                    return $element;
                }
            }, array_filter($table['group-order']));

            update_post_meta($new_table_id, '_groups', $table_g_order);

            // Insert tables into products
            if (isset($table['products'])) {
                foreach ($table['products'] as $product) {
                    // Modify IDs
                    foreach ($product['table'] as $idx => $table_gp) {
                        if (isset($ids_map[$table_gp['group_id']])) {
                            $product['table'][$idx]['old_id'] = $product['table'][$idx]['group_id'];
                            $product['table'][$idx]['group_id'] = $ids_map[$table_gp['group_id']];
                        }

                        foreach ($table_gp['attributes'] as $idx_att => $table_att) {
                            if (isset($ids_map[$table_att['attr_id']])) {
                                $product['table'][$idx]['attributes'][$idx_att]['old_id'] = $product['table'][$idx]['attributes'][$idx_att]['attr_id'];
                                $product['table'][$idx]['attributes'][$idx_att]['attr_id'] = $ids_map[$table_att['attr_id']];
                            }
                        }
                    }
                    // #End modify IDs

                    // Get product by slug
                    $product_id = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type= %s AND post_status = 'publish'",
                            $product['slug'],
                            'product'
                        )
                    );

                    if ($product_id) {
                        update_post_meta($product_id, '_dwps_table', $new_table_id);
                        update_post_meta($product_id, '_dwps_specification_table', $product['table']);
                    }
                }
            }
        }
    }

    // phpcs:enable
}
