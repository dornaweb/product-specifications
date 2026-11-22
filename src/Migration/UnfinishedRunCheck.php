<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration;

use Throwable;

final class UnfinishedRunCheck
{
    public const OPTION = 'product_specifications_unfinished_run_checked';

    public function __construct(private readonly MigrationRunner $runner)
    {
    }

    public function perform(): void
    {
        if (get_option(self::OPTION) !== false) {
            return;
        }

        try {
            $this->runner->reopen();
        } catch (Throwable) {
            return;
        }

        update_option(self::OPTION, 1, true);
    }
}
