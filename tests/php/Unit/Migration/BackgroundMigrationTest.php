<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Migration;

use Amiut\ProductSpecs\Migration\BackgroundMigration;
use Amiut\ProductSpecs\Migration\LegacyGroupTaxonomy;
use Amiut\ProductSpecs\Migration\MigrationRunner;
use Amiut\ProductSpecs\Migration\MigrationRunRepository;
use Amiut\ProductSpecs\Upgrade\DataVersionRepository;
use PHPUnit\Framework\TestCase;

final class BackgroundMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['wp_test_options'] = [DataVersionRepository::OPTION => 1];
        $GLOBALS['wp_test_actions'] = [];
        $GLOBALS['wp_test_scheduled'] = [];
    }

    public function testStartingQueuesTheFirstEvent(): void
    {
        $this->background(new FakeStep('values', [1, 2, 3], [], []), 60.0)->start();

        self::assertArrayHasKey(BackgroundMigration::HOOK, $GLOBALS['wp_test_scheduled']);
    }

    public function testTheNextEventIsQueuedBeforeTheChunkRuns(): void
    {
        $step = new FakeStep('values', [1, 2, 3], [], []);
        $background = $this->background($step, 60.0);
        $background->start();
        $GLOBALS['wp_test_scheduled'] = [];

        $queuedDuringChunk = false;
        $step->onRun = static function () use (&$queuedDuringChunk): void {
            $queuedDuringChunk = isset($GLOBALS['wp_test_scheduled'][BackgroundMigration::HOOK]);
        };

        $background->process();

        self::assertTrue($queuedDuringChunk);
    }

    public function testARunLeftRunningByTheBudgetContinuesOnTheNextEvent(): void
    {
        $step = new FakeStep('values', [1, 2, 3, 4, 5], [], []);
        $background = $this->background($step, 0.0);
        $background->start();

        $background->process();
        $background->process();

        self::assertSame([0, 2], $step->cursors);
        self::assertArrayHasKey(BackgroundMigration::HOOK, $GLOBALS['wp_test_scheduled']);
    }

    public function testAnEventLeftQueuedAfterTheRunEndsDoesNothing(): void
    {
        $step = new FakeStep('values', [1, 2, 3], [], []);
        $background = $this->background($step, 60.0);
        $background->start();
        $background->process();

        self::assertTrue((new MigrationRunRepository())->stored()->isComplete());

        $GLOBALS['wp_test_scheduled'] = [];
        $background->process();

        self::assertSame([0, 2], $step->cursors);
        self::assertSame([], $GLOBALS['wp_test_scheduled']);
    }

    public function testAFailedRunIsNotCarriedForward(): void
    {
        $step = new FakeStep('values', [1, 2, 3], [], []);
        $background = $this->background($step, 60.0);
        $background->start();
        $step->throwOnNextRun = true;
        $background->process();

        self::assertTrue((new MigrationRunRepository())->stored()->isFailed());

        $GLOBALS['wp_test_scheduled'] = [];
        $background->process();

        self::assertSame([0], $step->cursors);
        self::assertSame([], $GLOBALS['wp_test_scheduled']);
    }

    public function testAPollRunsAChunkWhenTheQueuedEventIsLongOverdue(): void
    {
        $step = new FakeStep('values', [1, 2, 3], [], []);
        $background = $this->background($step, 60.0);
        $background->start();
        $GLOBALS['wp_test_scheduled'][BackgroundMigration::HOOK] = time() - BackgroundMigration::OVERDUE_AFTER - 1;

        $background->nudge();

        self::assertSame([0], $step->cursors);
    }

    public function testAPollLeavesAnOnTimeEventToCron(): void
    {
        $step = new FakeStep('values', [1, 2, 3], [], []);
        $background = $this->background($step, 60.0);
        $background->start();

        $background->nudge();

        self::assertSame([], $step->cursors);
    }

    private function background(FakeStep $step, float $budget): BackgroundMigration
    {
        return new BackgroundMigration(
            new MigrationRunner(
                new MigrationRunRepository(),
                new DataVersionRepository(),
                new LegacyGroupTaxonomy(),
                [$step],
                2
            ),
            $budget
        );
    }
}
