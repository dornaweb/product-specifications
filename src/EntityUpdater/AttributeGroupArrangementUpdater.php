<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\EntityUpdater;

final class AttributeGroupArrangementUpdater
{
    public function __invoke(): void
    {
        $groupId = (int) filter_input(INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT);
        $attributes = array_map(
            'absint',
            array_filter(
                (array) filter_input(INPUT_POST, 'attr', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FORCE_ARRAY)
            )
        );

        $check = update_term_meta($groupId, 'attributes', $attributes);

        if ($check instanceof \WP_Error) {
            wp_send_json_error(
                [
                    'message' => $check->get_error_message(),
                ]
            );
        }

        wp_send_json_success(
            [
                'message' => esc_html__('Attributes arrangement successfully updated.', 'product-specifications'),
            ]
        );
    }
}
