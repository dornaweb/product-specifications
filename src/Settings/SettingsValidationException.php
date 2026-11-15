<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Settings;

use RuntimeException;

final class SettingsValidationException extends RuntimeException
{
    /**
     * @param array<string, string> $errors
     */
    public function __construct(public readonly array $errors)
    {
        parent::__construct(__('Some settings could not be saved.', 'product-specifications'));
    }
}
