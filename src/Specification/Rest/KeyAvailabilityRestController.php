<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Rest;

use Amiut\ProductSpecs\Capability\Capabilities;
use Amiut\ProductSpecs\Specification\SlugAvailability;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\SpecificationSlug;
use WP_REST_Request;
use WP_REST_Server;

/**
 * What the create form asks while a key is typed, answered by the same rules the guard
 * applies on save, so the form and the server never disagree.
 */
final class KeyAvailabilityRestController
{
    public const NAMESPACE = 'product-specifications/v2';
    public const ROUTE = '/specifications/key-availability';

    public function __construct(
        private readonly SlugAvailability $availability,
        private readonly SpecificationDefinitionRepository $definitions,
        private readonly Capabilities $capabilities
    ) {
    }

    public function registerRoutes(): void
    {
        register_rest_route(self::NAMESPACE, self::ROUTE, [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'read'],
            'permission_callback' => [$this->capabilities, 'currentUserCanManage'],
            'args' => [
                'key' => ['type' => 'string', 'required' => true],
            ],
        ]);
    }

    /**
     * @return array{key: ?string, taken: bool, holder: ?string, suggestion: ?string}
     */
    public function read(WP_REST_Request $request): array
    {
        $typed = $request->get_param('key');
        $slug = is_string($typed) ? SpecificationSlug::fromInput($typed) : null;

        if ($slug === null) {
            return ['key' => null, 'taken' => false, 'holder' => null, 'suggestion' => null];
        }

        if (!$this->availability->taken($slug->value())) {
            return ['key' => $slug->value(), 'taken' => false, 'holder' => null, 'suggestion' => null];
        }

        return [
            'key' => $slug->value(),
            'taken' => true,
            'holder' => $this->definitions->findByKey($slug->value())?->name(),
            'suggestion' => $this->availability->nextFree($slug)->value(),
        ];
    }
}
