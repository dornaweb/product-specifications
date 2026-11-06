<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet\Rest;

use Amiut\ProductSpecs\SpecificationSet\SpecificationSetPostType;
use WP_REST_Request;

/**
 * The counts the sets list shows beside a set, which the collection itself
 * cannot express.
 */
final class SpecificationSetRestField
{
    public const FIELD = 'product_count';

    public function __construct(private readonly ProductUsageCounter $usage)
    {
    }

    public function register(): void
    {
        register_rest_field(
            SpecificationSetPostType::KEY,
            self::FIELD,
            [
                'get_callback' => [$this, 'read'],
                'schema' => [
                    'description' => __(
                        'How many products this set is assigned to.',
                        'product-specifications'
                    ),
                    'type' => 'integer',
                    'context' => ['view', 'edit'],
                    'readonly' => true,
                ],
            ]
        );
    }

    /**
     * `_fields` trims the response but does not stop this callback running, so
     * the expensive member guards itself.
     *
     * @param array<string, mixed> $post
     */
    public function read(array $post, string $field, WP_REST_Request $request): ?int
    {
        $fields = $request->get_param('_fields');

        if (is_string($fields) && $fields !== '' && !str_contains($fields, self::FIELD)) {
            return null;
        }

        $postId = is_numeric($post['id'] ?? null) ? (int) $post['id'] : 0;

        return $this->usage->forSet($postId);
    }
}
