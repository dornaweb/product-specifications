<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Admin;

interface PageRegistry
{
    public function register(Page $page): void;
}
