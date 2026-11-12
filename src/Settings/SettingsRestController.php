<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Settings;

use Amiut\ProductSpecs\Capability\Capabilities;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

final class SettingsRestController
{
    public const READ_CAPABILITY = 'edit_products';

    private const NAMESPACE = 'product-specifications/v2';
    private const ROUTE = '/settings';

    public function __construct(
        private readonly SettingsRepository $repository,
        private readonly Capabilities $capabilities
    ) {
    }

    public function registerRoutes(): void
    {
        register_rest_route(
            self::NAMESPACE,
            self::ROUTE,
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [$this, 'read'],
                    'permission_callback' => [$this, 'canRead'],
                ],
                [
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => [$this, 'update'],
                    'permission_callback' => [$this, 'canUpdate'],
                ],
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function read(): array
    {
        return $this->repository
            ->snapshot()
            ->toRestResponse($this->capabilities->currentUserCanManage());
    }

    /**
     * @return array<string, mixed>|WP_Error
     */
    public function update(WP_REST_Request $request): array|WP_Error
    {
        $settings = $request->get_param('settings');
        if (!is_array($settings)) {
            return new WP_Error(
                'product_specifications_invalid_settings',
                __('Send the complete settings object.', 'product-specifications'),
                ['status' => 400]
            );
        }

        $values = [];
        foreach ($settings as $key => $value) {
            if (!is_string($key)) {
                return new WP_Error(
                    'product_specifications_invalid_settings',
                    __('Every setting must have a string key.', 'product-specifications'),
                    ['status' => 400]
                );
            }

            $values[$key] = $value;
        }

        try {
            return $this->repository
                ->save($values)
                ->toRestResponse(true);
        } catch (SettingsValidationException $exception) {
            return new WP_Error(
                'product_specifications_invalid_settings',
                $exception->getMessage(),
                [
                    'status' => 400,
                    'fields' => $exception->errors,
                ]
            );
        }
    }

    public function canRead(): bool
    {
        return current_user_can(self::READ_CAPABILITY)
            || $this->capabilities->currentUserCanManage();
    }

    public function canUpdate(): bool
    {
        return $this->capabilities->currentUserCanManage();
    }
}
