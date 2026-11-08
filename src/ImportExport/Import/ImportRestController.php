<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport\Import;

use Amiut\ProductSpecs\Capability\Capabilities;
use Amiut\ProductSpecs\ImportExport\InvalidPackage;
use RuntimeException;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

final class ImportRestController
{
    public const NAMESPACE = 'product-specifications/v2';
    public const ROUTE = '/package/import';

    public function __construct(
        private readonly ImportRunner $runner,
        private readonly BackgroundImport $background,
        private readonly Capabilities $capabilities
    ) {
    }

    public function registerRoutes(): void
    {
        $permission = [$this->capabilities, 'currentUserCanManage'];

        register_rest_route(self::NAMESPACE, self::ROUTE, [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => fn (): array => $this->payload($this->background->nudge()),
                'permission_callback' => $permission,
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'upload'],
                'permission_callback' => $permission,
            ],
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => function (): array {
                    $this->runner->discard();

                    return $this->payload($this->runner->run());
                },
                'permission_callback' => $permission,
            ],
        ]);

        register_rest_route(self::NAMESPACE, self::ROUTE . '/report', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => fn (): array|WP_Error => $this->report(),
            'permission_callback' => $permission,
        ]);

        register_rest_route(self::NAMESPACE, self::ROUTE . '/start', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => fn (): array => $this->payload($this->background->start()),
            'permission_callback' => $permission,
        ]);
    }

    /**
     * @param WP_REST_Request<array<string, mixed>> $request
     * @return array<string, mixed>|WP_Error
     */
    public function upload(WP_REST_Request $request): array|WP_Error
    {
        $file = $request->get_file_params()['file'] ?? null;
        $file = is_array($file) ? $file : [];
        $path = is_string($file['tmp_name'] ?? null) ? $file['tmp_name'] : '';

        if ($path === '' || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($path)) {
            return new WP_Error(
                'product_specifications_import_no_file',
                __('Choose a package file to import.', 'product-specifications'),
                ['status' => 400]
            );
        }

        // phpcs:ignore WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsUnknown -- A local upload.
        $json = (string) file_get_contents($path);
        $fileName = is_string($file['name'] ?? null) ? sanitize_file_name($file['name']) : '';

        try {
            $run = $this->runner->prepare($json, $fileName);
        } catch (InvalidPackage $invalid) {
            return new WP_Error(
                'product_specifications_import_invalid',
                __('The file cannot be imported.', 'product-specifications'),
                ['status' => 400, 'errors' => $invalid->errors()]
            );
        } catch (RuntimeException $exception) {
            return new WP_Error(
                'product_specifications_import_unavailable',
                $exception->getMessage(),
                ['status' => 409]
            );
        }

        return $this->payload($run);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(?ImportRun $run): array
    {
        if ($run === null) {
            return ['run' => null];
        }

        return [
            'run' => [
                'status' => $run->status(),
                'file_name' => $run->fileName(),
                'processed' => $run->processed(),
                'total' => $run->total(),
                'failure' => $run->failure(),
                'unknown_sections' => $run->unknownSections(),
                ...$run->summary()->toArray(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>|WP_Error
     */
    private function report(): array|WP_Error
    {
        $run = $this->runner->run();
        $report = $this->runner->report();

        if ($run === null || $report === null) {
            return new WP_Error(
                'product_specifications_import_no_report',
                __('There is no finished import to report on.', 'product-specifications'),
                ['status' => 404]
            );
        }

        return [
            'file' => $run->fileName(),
            'counts' => $run->summary()->counts(),
            'sections' => $report,
        ];
    }
}
