<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration;

use Amiut\ProductSpecs\Capability\Capabilities;
use Amiut\ProductSpecs\Upgrade\DataVersionRepository;
use WP_REST_Server;

final class MigrationRestController
{
    public const NAMESPACE = 'product-specifications/v2';
    public const ROUTE = '/migration';

    public function __construct(
        private readonly MigrationRunner $runner,
        private readonly BackgroundMigration $background,
        private readonly DataVersionRepository $versions,
        private readonly Capabilities $capabilities
    ) {
    }

    public function registerRoutes(): void
    {
        $permission = [$this->capabilities, 'currentUserCanManage'];

        register_rest_route(self::NAMESPACE, self::ROUTE, [
            'methods' => WP_REST_Server::READABLE,
            'callback' => fn (): array => $this->payload($this->background->nudge()),
            'permission_callback' => $permission,
        ]);

        register_rest_route(self::NAMESPACE, self::ROUTE . '/start', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => fn (): array => $this->payload($this->background->start()),
            'permission_callback' => $permission,
        ]);

        register_rest_route(self::NAMESPACE, self::ROUTE . '/resume', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => fn (): array => $this->payload($this->background->resume()),
            'permission_callback' => $permission,
        ]);
    }

    /**
     * The data version is read fresh rather than through MigrationState, which keeps the
     * value it read first, because the chunk run by this same request can finish the run.
     *
     * @return array<string, mixed>
     */
    private function payload(MigrationRun $run): array
    {
        return [
            'migrated' => $this->versions->stored()->isMigrated(),
            'run' => $run->toStorage(),
            'steps' => $run->isRunning() ? null : $this->runner->scope(),
        ];
    }
}
