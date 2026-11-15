<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Upgrade;

use Amiut\ProductSpecs\Upgrade\DataVersion;
use Amiut\ProductSpecs\Upgrade\DataVersionRepository;
use Amiut\ProductSpecs\Upgrade\Installer;
use Amiut\ProductSpecs\Upgrade\LegacyDataProbe;
use Amiut\ProductSpecs\Migration\MigrationState;
use PHPUnit\Framework\TestCase;

final class InstallerTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['wp_test_options'] = [];
        $GLOBALS['wp_test_posts'] = [];
    }

    public function testAFreshSiteIsRecordedAsMigrated(): void
    {
        $this->install();

        static::assertTrue($this->migrationState()->isMigrated());
    }

    public function testASiteWithSpecificationSetPostsNeedsMigration(): void
    {
        $GLOBALS['wp_test_posts'] = [12];

        $this->install();

        static::assertTrue($this->migrationState()->requiresMigration());
    }

    public function testASiteWithLegacySettingsNeedsMigration(): void
    {
        $GLOBALS['wp_test_options']['dwps_tab_title'] = 'Specs';

        $this->install();

        static::assertTrue($this->migrationState()->requiresMigration());
    }

    /**
     * Reactivating a migrated site must not reset its stored version. The probe
     * would find nothing left to migrate and stamp it back down, which would
     * offer the user a migration that destroys the migrated data.
     */
    public function testAStoredVersionIsNeverOverwritten(): void
    {
        $GLOBALS['wp_test_options'][DataVersionRepository::OPTION] = DataVersion::CURRENT_VERSION;
        $GLOBALS['wp_test_posts'] = [12];

        $this->install();

        static::assertTrue($this->migrationState()->isMigrated());
    }

    public function testRunningInstallTwiceKeepsTheFirstAnswer(): void
    {
        $GLOBALS['wp_test_posts'] = [12];
        $this->install();

        $GLOBALS['wp_test_posts'] = [];
        $this->install();

        static::assertTrue($this->migrationState()->requiresMigration());
    }

    private function install(): void
    {
        (new Installer(new DataVersionRepository(), new LegacyDataProbe()))->install();
    }

    private function migrationState(): MigrationState
    {
        return new MigrationState(new DataVersionRepository());
    }
}
