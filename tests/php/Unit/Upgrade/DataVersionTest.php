<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Upgrade;

use Amiut\ProductSpecs\Upgrade\DataVersion;
use PHPUnit\Framework\TestCase;

/**
 * Reading a damaged option as "already migrated" would skip the migration and
 * destroy unmigrated data, so every unusable value has to land on the low side.
 */
final class DataVersionTest extends TestCase
{
    /**
     * @return array<string, array{mixed}>
     */
    public static function unusableStoredValueProvider(): array
    {
        return [
            'option absent' => [null],
            'option never set' => [false],
            'empty string' => [''],
            'not a number' => ['broken'],
            'array' => [[2]],
            'float' => [2.0],
            'zero' => [0],
            'negative' => [-1],
            'numeric string below the floor' => ['0'],
        ];
    }

    /**
     * @dataProvider unusableStoredValueProvider
     */
    public function testAnUnusableStoredValueReadsAsPendingMigration(mixed $stored): void
    {
        $version = DataVersion::fromStored($stored);

        static::assertFalse($version->isMigrated());
        static::assertSame(DataVersion::LEGACY_VERSION, $version->value());
    }

    /**
     * @return array<string, array{mixed, int}>
     */
    public static function usableStoredValueProvider(): array
    {
        return [
            'legacy as int' => [1, 1],
            'current as int' => [2, 2],
            'current as string' => ['2', 2],
            'ahead of this build' => [99, 99],
        ];
    }

    /**
     * @dataProvider usableStoredValueProvider
     */
    public function testAUsableStoredValueIsKept(mixed $stored, int $expected): void
    {
        static::assertSame($expected, DataVersion::fromStored($stored)->value());
    }

    public function testOnlyTheCurrentVersionAndAboveCountAsMigrated(): void
    {
        static::assertFalse(DataVersion::fromStored(1)->isMigrated());
        static::assertTrue(DataVersion::fromStored(2)->isMigrated());
        static::assertTrue(DataVersion::fromStored(99)->isMigrated());
    }

    public function testAFreshInstallIsMigratedAndALegacyInstallIsNot(): void
    {
        static::assertTrue(DataVersion::forInstall(false)->isMigrated());
        static::assertFalse(DataVersion::forInstall(true)->isMigrated());
    }
}
