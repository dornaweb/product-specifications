<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ProductValues;

use RuntimeException;

/**
 * Thrown when saving specification values, such as through
 * `\Amiut\ProductSpecs\saveSpecifications()`, and a specification's type rejects a value.
 * Nothing is written when it is thrown.
 */
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
     * Why each rejected value was rejected.
     *
     * @return array<string, string> Each key is the key of a specification whose value was
     *                               rejected; each value is its type's translated message,
     *                               such as "Choose one of the options."
     */
    public function messages(): array
    {
        return $this->messages;
    }
}
