<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Capability;

final class Capabilities
{
    public const DEFAULT_CAPABILITY = 'manage_woocommerce';
    public const FILTER = 'product_specifications_capability';

    private function __construct(private readonly string $capability)
    {
    }

    /**
     * A filter that returns nothing usable must not become the gate: an empty
     * capability denies every user, administrators included.
     */
    public static function fromFilterValue(mixed $value): self
    {
        return new self(
            is_string($value) && $value !== '' ? $value : self::DEFAULT_CAPABILITY
        );
    }

    public function currentUserCanManage(): bool
    {
        return current_user_can($this->capability);
    }

    public function manageSpecifications(): string
    {
        return $this->capability;
    }
}
