<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Migration;

use Amiut\ProductSpecs\Migration\LegacyGroupTaxonomy;
use Amiut\ProductSpecs\Migration\MigrationRun;
use Amiut\ProductSpecs\Migration\MigrationRunner;
use Amiut\ProductSpecs\Migration\MigrationRunRepository;
use Amiut\ProductSpecs\Migration\UnfinishedRunCheck;
use Amiut\ProductSpecs\Upgrade\DataVersionRepository;
use PHPUnit\Framework\TestCase;

final class UnfinishedRunCheckTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['wp_test_options'] = [DataVersionRepository::OPTION => 2];
        (new MigrationRunRepository())->store(MigrationRun::started('values', 5, 1)->completed());
    }

    public function testACheckTheDatabaseCouldNotAnswerIsTriedAgain(): void
    {
        $values = new FakeStep('values', [10], [], []);
        $values->throwOnTotal = true;
        $check = $this->check($values);

        $check->perform();

        self::assertArrayNotHasKey(UnfinishedRunCheck::OPTION, $GLOBALS['wp_test_options']);
        self::assertSame(2, $GLOBALS['wp_test_options'][DataVersionRepository::OPTION]);

        $values->throwOnTotal = false;
        $check->perform();

        self::assertSame(1, $GLOBALS['wp_test_options'][DataVersionRepository::OPTION]);
        self::assertSame(1, $GLOBALS['wp_test_options'][UnfinishedRunCheck::OPTION]);
    }

    public function testASiteSetBackIsNotSetBackAgainAfterItsNewRun(): void
    {
        $check = $this->check(new FakeStep('values', [10], [], []));
        $check->perform();

        $GLOBALS['wp_test_options'][DataVersionRepository::OPTION] = 2;
        $check->perform();

        self::assertSame(2, $GLOBALS['wp_test_options'][DataVersionRepository::OPTION]);
    }

    private function check(FakeStep $step): UnfinishedRunCheck
    {
        return new UnfinishedRunCheck(
            new MigrationRunner(
                new MigrationRunRepository(),
                new DataVersionRepository(),
                new LegacyGroupTaxonomy(),
                [$step],
                2
            )
        );
    }
}
