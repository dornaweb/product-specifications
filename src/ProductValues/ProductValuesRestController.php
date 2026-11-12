<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ProductValues;

use RuntimeException;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

final class ProductValuesRestController
{
    use RequestsRouteId;

    private const NAMESPACE = 'product-specifications/v2';

    public function __construct(
        private readonly ProductValueRepository $repository,
        private readonly ProductValueWriter $writer
    ) {
    }

    public function registerRoutes(): void
    {
        register_rest_route(
            self::NAMESPACE,
            '/products/(?P<id>\d+)/specifications',
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [$this, 'read'],
                    'permission_callback' => [$this, 'canEdit'],
                ],
                [
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => [$this, 'update'],
                    'permission_callback' => [$this, 'canEdit'],
                ],
            ]
        );
    }

    /**
     * @return array{set: ?string, values: array{schema_version: int, values: array<string, mixed>}}
     */
    public function read(WP_REST_Request $request): array
    {
        $postId = $this->requestId($request);

        return [
            'set' => $this->repository->assignedSet($postId),
            'values' => $this->repository->read($postId)->toStorage(),
        ];
    }

    /**
     * @return array{set: ?string, values: array{schema_version: int, values: array<string, mixed>}}|WP_Error
     */
    public function update(WP_REST_Request $request): array|WP_Error
    {
        $set = $request->get_param('set');
        $values = $request->get_param('values');

        if ($set !== null && !is_string($set)) {
            return $this->invalid(__('The specification set must be a slug or null.', 'product-specifications'));
        }

        if (!is_array($values)) {
            return $this->invalid(__('Send the complete specification value map.', 'product-specifications'));
        }

        $valueMap = [];
        foreach ($values as $key => $value) {
            if (!is_string($key)) {
                return $this->invalid(__('Every specification value needs a string key.', 'product-specifications'));
            }

            $valueMap[$key] = $value;
        }

        $setSlug = $set === null ? null : trim($set);

        try {
            $saved = $this->writer->replace($this->requestId($request), $setSlug, $valueMap);
        } catch (RejectedValues $rejected) {
            return new WP_Error(
                'product_specifications_rejected_values',
                $rejected->getMessage(),
                ['status' => 400, 'errors' => $rejected->messages()]
            );
        } catch (RuntimeException $exception) {
            return new WP_Error(
                'product_specifications_value_save_failed',
                $exception->getMessage(),
                ['status' => 500]
            );
        }

        return [
            'set' => $setSlug,
            'values' => ProductValueDocument::fromValues($saved)->toStorage(),
        ];
    }

    public function canEdit(WP_REST_Request $request): bool
    {
        return current_user_can('edit_post', $this->requestId($request));
    }

    private function invalid(string $message): WP_Error
    {
        return new WP_Error(
            'product_specifications_invalid_values',
            $message,
            ['status' => 400]
        );
    }
}
