<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Rest;

use WP_REST_Request;

final class SpecificationCollectionQuery
{
    public const TYPE_PARAM = 'specification_type';

    public function register(): void
    {
        add_filter('rest_spec-attr_collection_params', [$this, 'collectionParams']);
        add_filter('rest_spec-attr_query', [$this, 'query'], 10, 2);
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function collectionParams(array $params): array
    {
        $params[self::TYPE_PARAM] = [
            'description' => __('Limit results to one specification type.', 'product-specifications'),
            'type' => 'string',
            'sanitize_callback' => 'sanitize_key',
        ];

        return $params;
    }

    /**
     * @param array<string, mixed> $args
     * @return array<string, mixed>
     */
    public function query(array $args, WP_REST_Request $request): array
    {
        $type = $request->get_param(self::TYPE_PARAM);

        if (!is_string($type) || $type === '') {
            return $args;
        }

        $args['meta_query'] = [
            [
                'key' => 'attr_type',
                'value' => $type,
                'compare' => '=',
            ],
        ];

        return $args;
    }
}
