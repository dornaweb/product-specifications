<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Migration;

use Amiut\ProductSpecs\Migration\MigrationRun;
use Amiut\ProductSpecs\Migration\MigrationRunner;
use Amiut\ProductSpecs\Migration\MigrationRunRepository;
use Amiut\ProductSpecs\Migration\MigrationStep;
use Amiut\ProductSpecs\Upgrade\DataVersionRepository;
use PHPUnit\Framework\TestCase;

final class MigrationRunnerTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['wp_test_options'] = [DataVersionRepository::OPTION => 1];
        $GLOBALS['wp_test_actions'] = [];
    }

    public function testChunksCarryAcrossTheStepBoundaryInRegistrationOrder(): void
    {
        $definitions = $this->step('definitions', [1, 2, 3]);
        $values = $this->step('values', [10, 11]);
        $runner = $this->runner([$definitions, $values]);

        self::assertSame(5, $runner->start()->total());

        $runner->runChunk();
        $runner->runChunk();
        $run = $runner->runChunk();

        self::assertTrue($run->isComplete());
        self::assertSame(5, $run->processed());
        self::assertSame([0, 2], $definitions->cursors);
        self::assertSame([0], $values->cursors);
    }

    public function testErrorsAndSkipsAreReportedAndTheRunStillSwitchesTheSite(): void
    {
        $runner = $this->runner([$this->step('values', [1, 2, 3], skip: [2], fail: [3])]);

        $runner->start();
        $runner->runChunk();
        $run = $runner->runChunk();
        $runner->runChunk();

        self::assertTrue($run->isComplete());
        self::assertSame([['step' => 'values', 'item' => 2]], $run->skipped());
        self::assertSame([['step' => 'values', 'item' => 3, 'message' => 'broken']], $run->errors());
        self::assertSame(2, $GLOBALS['wp_test_options'][DataVersionRepository::OPTION]);
        self::assertSame(
            [[MigrationRunner::REBUILD_ACTION, []]],
            $GLOBALS['wp_test_actions']
        );
    }

    public function testAThrowingStepFailsTheRunAndResumeContinuesFromTheStoredCursor(): void
    {
        $values = $this->step('values', [1, 2, 3, 4, 5]);
        $runner = $this->runner([$values]);

        $runner->start();
        $runner->runChunk();
        $values->throwOnNextRun = true;
        $failed = $runner->runChunk();

        self::assertTrue($failed->isFailed());
        self::assertSame('database went away', $failed->failure());
        self::assertSame(1, $GLOBALS['wp_test_options'][DataVersionRepository::OPTION]);

        $runner->resume();
        $runner->runChunk();

        self::assertSame([0, 2, 2], $values->cursors);
        self::assertSame(4, $runner->run()->processed());
    }

    public function testStartLeavesAMigratedSiteAnActiveRunAndAnEmptyStepListAlone(): void
    {
        self::assertSame(MigrationRun::IDLE, $this->runner([])->start()->status());

        $runner = $this->runner([$this->step('values', [1, 2, 3])]);
        $runner->start();
        $runner->runChunk();

        self::assertSame(2, $runner->start()->processed());

        $GLOBALS['wp_test_options'] = [DataVersionRepository::OPTION => 2];

        self::assertSame(MigrationRun::IDLE, $runner->start()->status());
    }

    /**
     * @param list<MigrationStep> $steps
     */
    private function runner(array $steps): MigrationRunner
    {
        return new MigrationRunner(new MigrationRunRepository(), new DataVersionRepository(), $steps, 2);
    }

    /**
     * @param list<int> $ids
     * @param list<int> $skip
     * @param list<int> $fail
     */
    private function step(string $key, array $ids, array $skip = [], array $fail = []): FakeStep
    {
        return new FakeStep($key, $ids, $skip, $fail);
    }
}
