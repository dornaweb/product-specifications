<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Migration\Soft;

use Amiut\ProductSpecs\Migration\Soft\LegacySettingsMigration;
use Amiut\ProductSpecs\Settings\SettingsRepository;
use PHPUnit\Framework\TestCase;

final class LegacySettingsMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['wp_test_options'] = [];
    }

    public function testEachStoredOptionIsCopiedUnderItsSettingKey(): void
    {
        $GLOBALS['wp_test_options'] = [
            'dwps_tab_title' => 'Specs',
            'dwps_wc_default_specs' => 'keep',
            'dwps_disable_default_styles' => 'on',
        ];

        (new LegacySettingsMigration())->migrate();

        static::assertSame(
            ['tab_title' => 'Specs', 'wc_default_specs' => 'keep', 'load_default_styles' => false],
            $GLOBALS['wp_test_options'][SettingsRepository::OPTION_NAME]
        );
    }

    public function testStylesStayOnUnlessTheyWereDisabled(): void
    {
        $GLOBALS['wp_test_options'] = ['dwps_disable_default_styles' => 'off'];

        (new LegacySettingsMigration())->migrate();

        static::assertSame(
            ['load_default_styles' => true],
            $GLOBALS['wp_test_options'][SettingsRepository::OPTION_NAME]
        );
    }

    public function testSavedSettingsAreNeverReplaced(): void
    {
        $saved = ['tab_title' => 'Details'];
        $GLOBALS['wp_test_options'] = [
            SettingsRepository::OPTION_NAME => $saved,
            'dwps_tab_title' => 'Specs',
        ];

        (new LegacySettingsMigration())->migrate();

        static::assertSame($saved, $GLOBALS['wp_test_options'][SettingsRepository::OPTION_NAME]);
    }

    public function testAnUnreadableSavedValueDoesNotBlockTheCopy(): void
    {
        $GLOBALS['wp_test_options'] = [
            SettingsRepository::OPTION_NAME => '',
            'dwps_tab_title' => 'Specs',
        ];

        (new LegacySettingsMigration())->migrate();

        static::assertSame(['tab_title' => 'Specs'], $GLOBALS['wp_test_options'][SettingsRepository::OPTION_NAME]);
    }

    public function testNothingIsWrittenWithoutStoredOptions(): void
    {
        (new LegacySettingsMigration())->migrate();

        static::assertArrayNotHasKey(SettingsRepository::OPTION_NAME, $GLOBALS['wp_test_options']);
    }
}
