<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Metabox;

use WP_Post;

interface Metabox
{
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_CORE = 'core';
    public const PRIORITY_DEFAULT = 'default';
    public const CONTEXT_NORMAL = 'normal';
    public const CONTEXT_SIDE = 'side';
    public const CONTEXT_ADVANCED = 'advanced';

    public function id(): string;

    public function title(): string;

    /**
     * @return 'normal'|'side'|'advanced'
     */
    public function context(): string;

    /**
     * @return 'core'|'default'|'high'|'low'
     */
    public function priority(): string;

    public function enabled(WP_Post $post): bool;

    public function render(WP_Post $post): string;

    public function action(WP_Post $post): void;
}
