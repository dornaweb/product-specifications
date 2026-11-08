<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ProductValues;

use RuntimeException;

final class RejectedValues extends RuntimeException
{
    /**
     * @param array<string, string> $messages Keyed by specification key.
     */
    public function __construct(private readonly array $messages)
    {
        parent::__construct(__('Some specification values could not be saved.', 'product-specifications'));
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->messages;
    }
}
