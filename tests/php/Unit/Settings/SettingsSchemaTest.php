<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Settings;

use Amiut\ProductSpecs\Block\StylesheetSettings;
use Amiut\ProductSpecs\Comparison\ComparisonSettings;
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
            ->with(new StylesheetSettings())
            ->with(new ComparisonSettings());

        $settings = (new SettingsSchema($providers))->settings([]);

        static::assertSame('remove_if_specs_not_empty', $settings->wooCommerceDefaultSpecifications());
        static::assertTrue($settings->loadDefaultStyles());
        static::assertSame(0, $settings->comparePageId());
        static::assertSame('Compare', $settings->compareButtonLabel());
        static::assertSame(3, $settings->maximumComparedProducts());
    }

    /**
     * @dataProvider invalidPages
     */
    public function testAPageSettingAcceptsOnlyAnIdOfZeroOrMore(mixed $value): void
    {
        $field = new SettingField(
            key: 'page',
            type: SettingType::PAGE,
            section: SettingSection::COMPARISON,
            label: 'Page',
            description: '',
            default: 0
        );

        static::assertFalse($field->sanitize($value)->valid);
        static::assertSame(12, $field->sanitize(12)->value);
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function invalidPages(): array
    {
        return [
            'a negative id' => [-1],
            'a numeric string' => ['12'],
            'a fraction' => [1.5],
        ];
    }

    /**
     * PHP turns a numeric array key into an integer, and the admin matches a stored value
     * to its option by strict string comparison.
     */
    public function testNumericOptionValuesReachTheAdminAsStrings(): void
    {
        $field = (new ComparisonSettings())->fields()[3];

        static::assertSame('3', $field->toRestSchema()['options'][1]['value']);
    }

    public function testAnOutOfRangeMaximumFallsBackToTheDefault(): void
    {
        $providers = (new SettingsProviders())
            ->with(new ProductTabSettings())
            ->with(new StylesheetSettings())
            ->with(new ComparisonSettings());

        $settings = (new SettingsSchema($providers))->settings(['compare_max_products' => '50']);

        static::assertSame(3, $settings->maximumComparedProducts());
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
