<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\EntityUpdater;

use Amiut\ProductSpecs\Content\Taxonomy;

final class AttributeController
{
    public const ACTION_ATTRIBUTES_MODIFIED = 'dwspecs_attributes_modified';
    public const ACTION_ATTRIBUTES_ADDED = 'product_specs.entities.attribute_added';
    public const ACTION_ATTRIBUTES_BEFORE_DELETE = 'product_specs.entities.before_delete';
    public const ACTION_ATTRIBUTES_DELETED = 'product_specs.entities.attribute_deleted';
    public const ACTION_ATTRIBUTES_UPDATED = 'product_specs.entities.attribute_updated';
    public const AJAX_ACTION = 'dwps_modify_attributes';
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

    // phpcs:ignore Inpsyde.CodeQuality.FunctionLength.TooLong
    private function processAdd(array $data): void
    {
        try {
            $this->validate($data);
            $check = wp_insert_term(
                $data['name'],
                Taxonomy\Attribute::KEY,
                [
                    'description' => $data['description'],
                    'slug' => $data['slug'],
                ]
            );

            if ($check instanceof \WP_Error) {
                wp_send_json_error(
                    [
                        'result' => 'error',
                        'message' => esc_html(
                            sprintf(
                                // translators: %s is the failed reason
                                __('Unable to create the attribute: %s', 'product-specifications'),
                                $check->get_error_message(),
                            )
                        ),
                        'where' => [],
                        'action' => self::ACTION_ADD,
                    ]
                );
            }

            $this->updateTermMetas($data, $check['term_id']);

            do_action_deprecated(
                self::ACTION_ATTRIBUTES_MODIFIED,
                [
                    'action' => self::ACTION_ADD,
                    'ids' => [$check['term_id']],
                ],
                '0.8.0',
                self::ACTION_ATTRIBUTES_ADDED
            );

            do_action(self::ACTION_ATTRIBUTES_ADDED, $check['term_id']);

            wp_send_json_success(
                [
                    'result' => 'success',
                    'message' => esc_html__('Attribute added successfully', 'product-specifications'),
                    'action' => self::ACTION_ADD,
                ]
            );
        } catch (InvalidEntityPropertyException $exception) {
            wp_send_json_error(
                [
                    'result' => 'error',
                    'message' => $exception->getMessage(),
                    'where' => [$exception->where()],
                    'action' => self::ACTION_ADD,
                ]
            );
        }
    }

    // phpcs:ignore Inpsyde.CodeQuality.FunctionLength.TooLong
    private function processEdit(array $data): void
    {
        try {
            $this->validate($data);

            $attributeId = (int) filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

            if ($attributeId < 1) {
                wp_send_json_error(
                    [
                        'result' => 'error',
                        'message' => 'invalid request',
                        'where' => [],
                        'action' => self::ACTION_EDIT,
                    ]
                );
            }

            $check = wp_update_term(
                $attributeId,
                Taxonomy\Attribute::KEY,
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'slug' => $data['slug'],
                ]
            );

            if ($check instanceof \WP_Error) {
                wp_send_json_error(
                    [
                        'result' => 'error',
                        'message' => esc_html__('Unable to update the attribute', 'product-specifications'),
                        'where' => [],
                        'action' => self::ACTION_EDIT,
                    ]
                );
            }

            $this->updateTermMetas($data, $check['term_id']);

            do_action_deprecated(
                self::ACTION_ATTRIBUTES_MODIFIED,
                [
                    'action' => self::ACTION_EDIT,
                    'ids' => [$check['term_id']],
                ],
                '0.8.0',
                self::ACTION_ATTRIBUTES_UPDATED,
            );

            do_action(self::ACTION_ATTRIBUTES_UPDATED, $check['term_id']);

            wp_send_json_success(
                [
                    'result' => 'success',
                    'message' => esc_html__('Attribute updated successfully', 'product-specifications'),
                    'action' => self::ACTION_EDIT,
                ]
            );
        } catch (InvalidEntityPropertyException $exception) {
            wp_send_json_error(
                [
                    'result' => 'error',
                    'message' => $exception->getMessage(),
                    'where' => [$exception->where()],
                    'action' => self::ACTION_EDIT,
                ]
            );
        }
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
            $groupId = (int) get_term_meta($id, 'attr_group', true);

            /**
             * Fires before an attribute is deleted
             *
             * @param int $termId
             * @param int $groupId
             */
            do_action(self::ACTION_ATTRIBUTES_BEFORE_DELETE, $id, $groupId);

            $check = wp_delete_term($id, Taxonomy\Attribute::KEY);

            if ($check instanceof \WP_Error) {
                continue;
            }

            /**
             * Fires after an attribute is deleted
             *
             * @param int $termId
             * @param int $groupId
             */
            do_action(self::ACTION_ATTRIBUTES_DELETED, $id, $groupId);
        }

        do_action_deprecated(
            self::ACTION_ATTRIBUTES_MODIFIED,
            [
                'action' => self::ACTION_EDIT,
                'ids' => $ids,
            ],
            '0.8.0',
            self::ACTION_ATTRIBUTES_DELETED,
        );

        wp_send_json_success(
            [
                'result' => 'success',
                'message' => esc_html__('Attribute(s) deleted successfully', 'product-specifications'),
                'action' => 'delete',
            ]
        );
    }

    /**
     * @throws InvalidEntityPropertyException
     */
    private function validate(array $data): void
    {
        if (empty($data['name'])) {
            throw new InvalidEntityPropertyException(
                esc_html__('Please enter an attribute name', 'product-specifications'),
                'input#attr_name'
            );
        }

        if (empty($data['group'])) {
            throw new InvalidEntityPropertyException(
                esc_html__('Please select a group', 'product-specifications'),
                'input#attr_group'
            );
        }

        if (empty($data['type'])) {
            throw new InvalidEntityPropertyException(
                esc_html__('Please select a field type', 'product-specifications'),
                'input#attr_type'
            );
        }

        if (($data['type'] === 'select' || $data['type'] === 'radio') && (empty($data['values']))) {
            throw new InvalidEntityPropertyException(
                esc_html__('Please set some values for attribute', 'product-specifications'),
                'input#attr_values'
            );
        }
    }

    private function updateTermMetas(array $data, int $termId): void
    {
        $termMetaMapping = [
            'attr_group' => $data['group'],
            'attr_type' => $data['type'],
            'attr_values' => array_filter(array_map('trim', $data['values'])),
            'attr_default' => rawurldecode((string) $data['default']),
        ];

        foreach ($termMetaMapping as $metaKey => $metaValue) {
            update_term_meta($termId, $metaKey, $metaValue);
        }
    }

    private function formData(): array
    {
        $name = (string) filter_input(INPUT_POST, 'attr_name', FILTER_SANITIZE_SPECIAL_CHARS);
        $slug = (string) filter_input(INPUT_POST, 'attr_slug', FILTER_SANITIZE_SPECIAL_CHARS);
        $description = (string) filter_input(INPUT_POST, 'attr_desc', FILTER_SANITIZE_SPECIAL_CHARS);
        $group = (string) filter_input(INPUT_POST, 'attr_group', FILTER_SANITIZE_SPECIAL_CHARS);
        $type = (string) filter_input(INPUT_POST, 'attr_type', FILTER_SANITIZE_SPECIAL_CHARS);
        $values = array_filter(
            (array) filter_input(
                INPUT_POST,
                'attr_values',
                FILTER_SANITIZE_SPECIAL_CHARS,
                FILTER_FORCE_ARRAY
            )
        );
        $default = $this->defaultValue($type);
        $id = (int) filter_input(INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT) ?: 0;

        return [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'group' => $group,
            'type' => $type,
            'values' => $values,
            'default' => $default,
            'id' => $id,
        ];
    }

    /**
     * @return array<string>|string|bool
     */
    private function defaultValue(string $fieldType)
    {
        if ($fieldType === 'select' || $fieldType === 'radio') {
            return (array) filter_input(INPUT_POST, 'attr_default', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FORCE_ARRAY);
        }

        if ($fieldType === 'true_false') {
            return !!filter_input(INPUT_POST, 'attr_default', FILTER_SANITIZE_SPECIAL_CHARS);
        }

        return (string) filter_input(INPUT_POST, 'attr_default', FILTER_SANITIZE_SPECIAL_CHARS);
    }

    private function sanitizeAction(string $action): string
    {
        return in_array($action, [self::ACTION_ADD, self::ACTION_EDIT, self::ACTION_DELETE])
            ? $action
            : 'add';
    }
}
