<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Settings;

use LogicException;

final class SettingsProviders
{
    /**
     * @param list<SettingsProvider> $providers
     */
    public function __construct(private readonly array $providers = [])
    {
    }

    /**
     * A container extension that adds the provider to the collection.
     *
     * @return callable(mixed): self
     */
    public static function adding(SettingsProvider $provider): callable
    {
        return static function (mixed $providers) use ($provider): self {
            if (!$providers instanceof self) {
                throw new LogicException('The settings providers service must be a SettingsProviders collection.');
            }

            return $providers->with($provider);
        };
    }

    public function with(SettingsProvider $provider): self
    {
        return new self([...$this->providers, $provider]);
    }

    /**
     * @return list<SettingField>
     */
    public function fields(): array
    {
        return array_merge(
            [],
            ...array_map(static fn (SettingsProvider $provider): array => $provider->fields(), $this->providers)
        );
    }
}
