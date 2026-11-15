<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Admin;

use Amiut\ProductSpecs\Admin\AdminScreens;
use Amiut\ProductSpecs\Admin\Page;
use Amiut\ProductSpecs\Capability\Capabilities;
use Amiut\ProductSpecs\Migration\MigrationState;
use Amiut\ProductSpecs\Upgrade\DataVersion;
use Amiut\ProductSpecs\Upgrade\DataVersionRepository;
use PHPUnit\Framework\TestCase;

final class AdminScreensTest extends TestCase
{
    public function testTheMigrationScreenExistsOnlyWhileAMigrationIsPending(): void
    {
        self::assertContains('/product-specifications/migration', $this->paths(DataVersion::LEGACY_VERSION));
        self::assertNotContains('/product-specifications/migration', $this->paths(DataVersion::CURRENT_VERSION));
    }

    /**
     * @return list<string>
     */
    private function paths(int $dataVersion): array
    {
        $GLOBALS['wp_test_options'] = [DataVersionRepository::OPTION => $dataVersion];
        $screens = new AdminScreens(
            Capabilities::fromFilterValue(Capabilities::DEFAULT_CAPABILITY),
            new MigrationState(new DataVersionRepository())
        );

        return array_map(static fn (Page $page): string => $page->path(), $screens->pages());
    }
}
