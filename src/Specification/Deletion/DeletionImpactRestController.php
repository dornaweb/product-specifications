<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Deletion;

use Amiut\ProductSpecs\Capability\Capabilities;
use Amiut\ProductSpecs\ProductValues\ProductValueRepository;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

/**
 * How many products a deletion would rewrite. Answered only when the delete dialog
 * opens, never for a list.
 */
final class DeletionImpactRestController
{
    public const NAMESPACE = 'product-specifications/v2';
    public const ROUTE = '/specifications/(?P<id>\d+)/impact';

    public function __construct(
        private readonly SpecificationDefinitionRepository $definitions,
        private readonly ProductValueRepository $values,
        private readonly Capabilities $capabilities
    ) {
    }

    public function registerRoutes(): void
    {
        register_rest_route(self::NAMESPACE, self::ROUTE, [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'read'],
            'permission_callback' => [$this->capabilities, 'currentUserCanManage'],
        ]);
    }

    /**
     * @return array{products: int}|WP_Error
     */
    public function read(WP_REST_Request $request): array|WP_Error
    {
        $id = $request->get_param('id');
        $definition = is_numeric($id) ? $this->definitions->find((int) $id) : null;

        if ($definition === null) {
            return new WP_Error(
                'product_specifications_not_found',
                __('Specification not found.', 'product-specifications'),
                ['status' => 404]
            );
        }

        return ['products' => $this->values->countHolding($definition->key())];
    }
}
