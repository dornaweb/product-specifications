<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\EntityUpdater;

use Amiut\ProductSpecs\Content\Taxonomy;

final class AttributeGroupController
{
    public const AJAX_ACTION = 'dwps_modify_groups';
    private const ACTION_ADD = 'add';
    private const ACTION_EDIT = 'edit';
    private const ACTION_DELETE = 'delete';

    public function __invoke(): void
    {
        $action = $this->sanitizeAction(
            (string) filter_input(INPUT_POST, 'do', FILTER_SANITIZE_SPECIAL_CHARS)
        );
        $data = $this->formData();

        if ($action === self::ACTION_ADD) {
            $this->processAdd($data);
            return;
        }

        if ($action === self::ACTION_EDIT) {
            $this->processEdit($data);
            return;
        }

        if ($action === self::ACTION_DELETE) {
            $this->processDelete();
            return;
        }

        wp_send_json_error(
            [
                'result' => 'error',
                'message' => 'Invalid request',
                'where' => [],
                'action' => 'unknown',
            ]
        );
    }

    private function processAdd(array $data): void
    {
        if (empty($data['name'])) {
            wp_send_json_error(
                [
                    'result' => 'error',
                    'message' => esc_html__('Please enter a group name', 'product-specifications'),
                    'where' => ['input#group_name'],
                    'action' => 'add',
                ]
            );
        }

        $check = wp_insert_term(
            $data['name'],
            'spec-group',
            [
                'description' => $data['description'],
                'slug' => $data['slug'],
            ]
        );

        if (is_wp_error($check)) {
            wp_send_json_error(
                [
                    'result' => 'error',
                    'message' => esc_html__('Something went wrong', 'product-specifications'),
                    'action' => 'add',
                ]
            );
            return;
        }

        wp_send_json_success(
            [
                'result' => 'success',
                'message' => esc_html__('Group created successfully', 'product-specifications'),
                'action' => 'add',
            ]
        );
    }

    private function processEdit(array $data): void
    {
        if (empty($data['name'])) {
            wp_send_json_error(
                [
                    'result' => 'error',
                    'message' => esc_html__('Please enter a group name', 'product-specifications'),
                    'where' => ['input#group_name'],
                    'action' => 'add',
                ]
            );
        }

        if (! is_int($data['id']) || $data['id'] < 1) {
            wp_send_json_error(
                [
                    'result' => 'error',
                    'message' => esc_html__('Invalid Group', 'product-specifications'),
                    'where' => ['input#group_id'],
                    'action' => 'add',
                ]
            );
        }

        $check = wp_update_term(
            $data['id'],
            'spec-group',
            [
                'name' => $data['name'],
                'description' => $data['description'],
                'slug' => $data['slug'],
            ]
        );

        if (is_wp_error($check)) {
            wp_send_json_error(
                [
                    'result' => 'error',
                    'message' => esc_html__('Something went wrong', 'product-specifications'),
                    'action' => 'add',
                ]
            );
            return;
        }

        wp_send_json_success(
            [
                'result' => 'success',
                'message' => esc_html__('Group edited successfully', 'product-specifications'),
                'action' => 'add',
            ]
        );
    }

    private function processDelete(): void
    {
        $ids = array_map(
            'absint',
            (array) filter_input(
                INPUT_POST,
                'id',
                FILTER_SANITIZE_NUMBER_INT,
                FILTER_FORCE_ARRAY
            )
        );

        $check = null;

        foreach ($ids as $id) {
            $check = wp_delete_term($id, Taxonomy\AttributeGroup::KEY);
        }

        if ($check instanceof \WP_Error) {
            wp_send_json_error(
                [
                    'result' => 'error',
                    'message' => esc_html__('Could not delete group(s)', 'product-specifications'),
                    'action' => 'delete',
                ]
            );
            return;
        }

        wp_send_json_success(
            [
                'result' => 'success',
                'message' => esc_html__('Group(s) deleted successfully', 'product-specifications'),
                'action' => 'delete',
            ]
        );
    }

    private function formData(): array
    {
        $name = (string) filter_input(INPUT_POST, 'group_name', FILTER_SANITIZE_SPECIAL_CHARS);
        $slug = (string) filter_input(INPUT_POST, 'group_slug', FILTER_SANITIZE_SPECIAL_CHARS);
        $description = (string) filter_input(INPUT_POST, 'group_desc', FILTER_SANITIZE_SPECIAL_CHARS);
        $id = (int) filter_input(INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT) ?: 0;

        return [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'id' => $id,
        ];
    }

    private function sanitizeAction(string $action): string
    {
        return in_array($action, [self::ACTION_ADD, self::ACTION_EDIT, self::ACTION_DELETE])
            ? $action
            : 'add';
    }
}
