<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Upgrade;

final class DataVersion
{
    public const CURRENT_VERSION = 2;
    public const LEGACY_VERSION = 1;

    private function __construct(private readonly int $version)
    {
    }

    /**
     * Anything unusable reads as the legacy version. Guessing "already migrated"
     * on a damaged option skips a migration and loses data; guessing the other
     * way costs a re-run of something idempotent.
     */
    public static function fromStored(mixed $value): self
    {
        if (is_int($value)) {
            return new self(max($value, self::LEGACY_VERSION));
        }

        if (is_string($value) && ctype_digit($value)) {
            return new self(max((int) $value, self::LEGACY_VERSION));
        }

        return self::legacyInstall();
    }

    public static function freshInstall(): self
    {
        return new self(self::CURRENT_VERSION);
    }

    public static function legacyInstall(): self
    {
        return new self(self::LEGACY_VERSION);
    }

    public static function forInstall(bool $hasLegacyData): self
    {
        return $hasLegacyData ? self::legacyInstall() : self::freshInstall();
    }

    public function isMigrated(): bool
    {
        return $this->version >= self::CURRENT_VERSION;
    }

    public function value(): int
    {
        return $this->version;
    }
}
