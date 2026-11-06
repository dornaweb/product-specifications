<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Settings;

final class SettingValueResult
{
    /**
     * @param string|bool|int|float|list<string>|null $value
     */
    private function __construct(
        public readonly bool $valid,
        public readonly string|bool|int|float|array|null $value,
        public readonly ?string $error
    ) {
    }

    /**
     * @param string|bool|int|float|list<string> $value
     */
    public static function valid(string|bool|int|float|array $value): self
    {
        return new self(true, $value, null);
    }

    public static function invalid(string $error): self
    {
        return new self(false, null, $error);
    }
}
