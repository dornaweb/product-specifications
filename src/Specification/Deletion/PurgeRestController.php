<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Deletion;

use Amiut\ProductSpecs\Capability\Capabilities;
use WP_REST_Server;

final class PurgeRestController
{
    public const NAMESPACE = 'product-specifications/v2';
    public const ROUTE = '/purge';

    public function __construct(
        private readonly PurgeQueue $queue,
        private readonly BackgroundPurge $background,
        private readonly Capabilities $capabilities
    ) {
    }

    public function registerRoutes(): void
    {
        register_rest_route(self::NAMESPACE, self::ROUTE, [
            'methods' => WP_REST_Server::READABLE,
            'callback' => fn (): array => $this->payload(),
            'permission_callback' => [$this->capabilities, 'currentUserCanManage'],
        ]);
    }

    /**
     * Read while someone watches the work, so the worker is nudged on the way.
     *
     * @return array{pending: list<array{slug: string, failure: ?string}>}
     */
    private function payload(): array
    {
        $this->background->nudge();

        return [
            'pending' => array_map(
                static fn (PurgeEntry $entry): array => ['slug' => $entry->slug(), 'failure' => $entry->failure()],
                $this->queue->entries()
            ),
        ];
    }
}
