<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport;

final class ExportDataAjaxHandler
{
    /**
     * @return never
     */
    public function __invoke(): void
    {
        $nonce = sanitize_text_field(
            wp_unslash(
                (string) filter_input(
                    INPUT_POST,
                    'dws_ex_nonce',
                    FILTER_SANITIZE_SPECIAL_CHARS
                )
            )
        );

        if (!(bool) wp_verify_nonce($nonce, 'dwspecs_nonce_export')) {
            wp_die('Invalid Request');
        }

        $includeProducts = (bool) filter_input(
            INPUT_POST,
            'include_products',
            FILTER_SANITIZE_SPECIAL_CHARS
        );

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Disposition: attachment; filename="' . $this->fileName() . '"');

        echo $this->export('json', $includeProducts); //phpcs:ignore

        exit;
    }

    private function fileName(): string
    {

        return 'dw-specs-table-' . date('j-m-Y') . '-' . rand(1, 1000000) . '.json';
    }

    /**
     * @return array|false|string
     */
    private function export($format = 'array', $include_products = true)
    {

        $results = [];
        $i = 0;
        foreach (dwspecs_get_table_groups() as $table) {
            $results[$i] = [
                'table-title' => get_the_title($table['table_id']),
                'title-base64' => base64_encode(get_the_title($table['table_id'])),
                'table-slug' => get_post_field('post_name', $table['table_id']),
                'table-id' => $table['table_id'],
                'group-order' => array_map('absint', (array) get_post_meta($table['table_id'], '_groups', true)),
            ];

            $results[$i]['skleton'] = [];

            foreach ($table['groups'] as $group) {
                $attributes = [];

                foreach ((dwspecs_get_attributes_by_group($group['term_id']) ?: []) as $attr) {
                    $attributes[] = [
                        'title' => $attr->name,
                        'slug' => $attr->slug,
                        'id' => $attr->term_id,
                        'type' => get_term_meta($attr->term_id, 'attr_type', true),
                        'values' => get_term_meta($attr->term_id, 'attr_values', true),
                        'default' => get_term_meta($attr->term_id, 'attr_default', true),
                    ];
                }
                $group['attributes'] = $attributes;
                $group['attributes-order'] = get_term_meta($group['term_id'], 'attributes', true);
                $results[$i]['skleton'][] = $group;
            }

            if ($include_products) {
                $products_list = new \WP_Query([
                    'post_type' => 'product',
                    'showposts' => -1,
                    'meta_key' => '_dwps_table',
                    'meta_value' => $table['table_id'],
                ]);

                foreach ($products_list->posts as $product) {
                    $results[$i]['products'][] = [
                        'title' => $product->post_title,
                        'slug' => $product->post_name,
                        'table' => dwspecs_get_table_result($product->ID),
                    ];
                }
            }

            $i++;
        }

        if ($format === 'json') {
            return json_encode($results);
        } else {
            return $results;
        }
    }
}
