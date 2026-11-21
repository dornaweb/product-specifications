<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Settings;

use Amiut\ProductSpecs\Block\StylesheetSettings;
use Amiut\ProductSpecs\Comparison\ComparisonSettings;
use Amiut\ProductSpecs\Integration\WooCommerce\ProductTabSettings;
use Amiut\ProductSpecs\Settings\Settings;
use Amiut\ProductSpecs\Settings\SettingsProviders;
use Amiut\ProductSpecs\Settings\SettingsRepository;
use Amiut\ProductSpecs\Settings\SettingsSchema;
use Amiut\ProductSpecs\Settings\SettingsValidationException;
use PHPUnit\Framework\TestCase;

final class SettingsRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['wp_test_options'] = [
            SettingsRepository::OPTION_NAME => [
                'tab_title' => 'Specs',
                'compare_max_products' => '50',
                'kept_by_another_plugin' => 'yes',
            ],
        ];
    }

    public function testUpdatingOneSettingKeepsEveryOtherStoredValueAsItIs(): void
    {
        $this->repository()->update(Settings::COMPARE_PAGE, 12);

        self::assertSame(
            [
                'tab_title' => 'Specs',
                'compare_max_products' => '50',
                'kept_by_another_plugin' => 'yes',
                'compare_page' => 12,
            ],
            $GLOBALS['wp_test_options'][SettingsRepository::OPTION_NAME]
        );
    }

    public function testAnInvalidValueIsNotStored(): void
    {
        $this->expectException(SettingsValidationException::class);

        try {
            $this->repository()->update(Settings::COMPARE_PAGE, -3);
        } finally {
            self::assertArrayNotHasKey('compare_page', $GLOBALS['wp_test_options'][SettingsRepository::OPTION_NAME]);
        }
    }

    private function repository(): SettingsRepository
    {
        return new SettingsRepository(new SettingsSchema(
            (new SettingsProviders())
                ->with(new ProductTabSettings())
                ->with(new StylesheetSettings())
                ->with(new ComparisonSettings())
        ));
    }
}
