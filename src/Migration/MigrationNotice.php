<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration;

use Amiut\ProductSpecs\Capability\Capabilities;
use Amiut\ProductSpecs\Upgrade\MigrationState;

final class MigrationNotice
{
    public function __construct(
        private readonly MigrationState $state,
        private readonly MigrationRunRepository $runs,
        private readonly MigrationScreen $screen,
        private readonly Capabilities $capabilities
    ) {
    }

    /**
     * @return array{status: string, message: string, action: string, url: string}|null
     */
    public function content(): ?array
    {
        if (!$this->state->requiresMigration() || !$this->capabilities->currentUserCanManage()) {
            return null;
        }

        [$status, $message, $action] = $this->wording($this->runs->stored());

        return ['status' => $status, 'message' => $message, 'action' => $action, 'url' => $this->screen->url()];
    }

    public function render(): void
    {
        $content = $this->screen->isCurrent() ? null : $this->content();

        if ($content === null) {
            return;
        }

        printf(
            '<div class="notice notice-%1$s"><p>%2$s</p>'
            . '<p><a class="button button-primary" href="%3$s">%4$s</a></p></div>',
            esc_attr($content['status']),
            esc_html($content['message']),
            esc_url($content['url']),
            esc_html($content['action'])
        );
    }

    /**
     * @return array{string, string, string}
     */
    private function wording(MigrationRun $run): array
    {
        if ($run->isRunning()) {
            return [
                'info',
                __(
                    'Product Specifications is migrating your data. Specification tables return to your store when it finishes.',
                    'product-specifications'
                ),
                __('View progress', 'product-specifications'),
            ];
        }

        if ($run->isFailed()) {
            return [
                'error',
                __(
                    'The Product Specifications migration stopped before it finished. Your store shows no specification tables until it completes.',
                    'product-specifications'
                ),
                __('Review and resume', 'product-specifications'),
            ];
        }

        return [
            'warning',
            __(
                'Product Specifications needs to migrate your data. Until it does, your store shows no specification tables, the product editor shows them empty, and any product you edit is skipped by the migration.',
                'product-specifications'
            ),
            __('Start the migration', 'product-specifications'),
        ];
    }
}
