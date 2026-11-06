<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ProductValues;

use Amiut\ProductSpecs\Capability\Capabilities;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

final class DeletionImpactRestController
{
    use RequestsRouteId;

    private const NAMESPACE = 'product-specifications/v2';

    public function __construct(
        private readonly ProductValueRepository $values,
        private readonly SpecificationDefinitionRepository $definitions,
        private readonly Capabilities $capabilities
    ) {
    }

    public function registerRoutes(): void
    {
        register_rest_route(
            self::NAMESPACE,
            '/specifications/(?P<id>\d+)/deletion-impact',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'read'],
                'permission_callback' => [$this, 'canDelete'],
            ]
        );
    }

    /**
     * @return array{product_count: int}|WP_Error
     */
    public function read(WP_REST_Request $request): array|WP_Error
    {
        $definition = $this->definitions->find($this->requestId($request));

        if ($definition === null) {
            return new WP_Error(
                'product_specifications_not_found',
                __('Specification not found.', 'product-specifications'),
                ['status' => 404]
            );
        }

        return ['product_count' => $this->values->countContaining($definition->key())];
    }

    public function canDelete(): bool
    {
        return $this->capabilities->currentUserCanManage();
    }
}
