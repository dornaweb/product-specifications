<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Settings;

use Amiut\ProductSpecs\Block\StylesheetSettings;
use Amiut\ProductSpecs\Integration\WooCommerce\ProductTabSettings;
use Amiut\ProductSpecs\Settings\SettingField;
use Amiut\ProductSpecs\Settings\SettingSection;
use Amiut\ProductSpecs\Settings\SettingsProvider;
use Amiut\ProductSpecs\Settings\SettingsProviders;
use Amiut\ProductSpecs\Settings\SettingsSchema;
use Amiut\ProductSpecs\Settings\SettingType;
use LogicException;
use PHPUnit\Framework\TestCase;

final class SettingsSchemaTest extends TestCase
{
    /**
     * The settings object reads its values by key, so a key that no provider declares
     * fails only when a page is rendered.
     */
    public function testTheShippedProvidersDeclareEverySettingTheSettingsObjectReads(): void
    {
        $providers = (new SettingsProviders())
            ->with(new ProductTabSettings())
            ->with(new StylesheetSettings());

        $settings = (new SettingsSchema($providers))->settings([]);

        static::assertSame('remove_if_specs_not_empty', $settings->wooCommerceDefaultSpecifications());
        static::assertTrue($settings->loadDefaultStyles());
    }

    public function testAKeyDeclaredByTwoProvidersIsRefused(): void
    {
        $provider = new class implements SettingsProvider {
            public function fields(): array
            {
                return [
                    new SettingField(
                        key: 'tab_title',
                        type: SettingType::TEXT,
                        section: SettingSection::DISPLAY,
                        label: 'Tab title',
                        description: '',
                        default: ''
                    ),
                ];
            }
        };

        $providers = (new SettingsProviders())->with(new ProductTabSettings())->with($provider);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('tab_title');

        (new SettingsSchema($providers))->settings([]);
    }
}
