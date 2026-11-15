<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Deletion;

use Amiut\ProductSpecs\Specification\Deletion\ValuePurge;
use Amiut\ProductSpecs\Tests\Unit\Support\InMemoryProductValues;
use PHPUnit\Framework\TestCase;

final class ValuePurgeTest extends TestCase
{
    public function testTheKeyLeavesEveryMapAndAnEmptiedMapIsDeleted(): void
    {
        $values = new InMemoryProductValues();
        $values->maps = [
            1 => ['weight' => 1.4, 'wifi' => true],
            2 => ['weight' => 2.0],
            3 => ['wifi' => false],
        ];

        self::assertTrue((new ValuePurge($values))->purge('weight', 0, 200)->isFinished());
        self::assertSame([1 => ['wifi' => true], 3 => ['wifi' => false]], $values->maps);
    }

    public function testAMapThatCarriesTheKeyAsAValueIsLeftAlone(): void
    {
        $values = new InMemoryProductValues();
        $values->maps = [5 => ['material' => 'weight']];

        self::assertTrue((new ValuePurge($values))->purge('weight', 0, 200)->isFinished());
        self::assertSame([5 => ['material' => 'weight']], $values->maps);
    }

    public function testABatchThatLeavesMoreBehindReportsWhereToContinue(): void
    {
        $values = new InMemoryProductValues();
        $values->maps = [1 => ['weight' => 1], 2 => ['weight' => 2], 3 => ['weight' => 3]];
        $purge = new ValuePurge($values);

        $first = $purge->purge('weight', 0, 2);

        self::assertFalse($first->isFinished());
        self::assertSame(2, $first->cursor());
        self::assertSame([3 => ['weight' => 3]], $values->maps);
        self::assertTrue($purge->purge('weight', 2, 2)->isFinished());
        self::assertSame([], $values->maps);
    }

    public function testExactlyTheLimitFinishesInOneRun(): void
    {
        $values = new InMemoryProductValues();
        $values->maps = [1 => ['weight' => 1], 2 => ['weight' => 2]];

        self::assertTrue((new ValuePurge($values))->purge('weight', 0, 2)->isFinished());
        self::assertSame([], $values->maps);
    }

    public function testAFailedWriteStopsAtTheProductBeforeIt(): void
    {
        $values = new InMemoryProductValues();
        $values->maps = [1 => ['weight' => 1], 2 => ['weight' => 2], 3 => ['weight' => 3]];
        $values->failOnPostId = 2;

        $progress = (new ValuePurge($values))->purge('weight', 0, 200);

        self::assertFalse($progress->isFinished());
        self::assertSame(1, $progress->cursor());
        self::assertStringContainsString('product 2', (string) $progress->failure());
        self::assertSame([2 => ['weight' => 2], 3 => ['weight' => 3]], $values->maps);
    }
}
