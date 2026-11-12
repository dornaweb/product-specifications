<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Migration;

use Amiut\ProductSpecs\Migration\IdBatch;
use PHPUnit\Framework\TestCase;

final class IdBatchTest extends TestCase
{
    public function testTakesTheIdsAboveTheCursorInAscendingOrder(): void
    {
        $batch = IdBatch::after([9, 3, 7, 1, 5], 1, 2);

        self::assertSame([3, 5], $batch->ids());
        self::assertSame(5, $batch->result(0)->nextCursor());
    }

    public function testTheLastBatchReportsNoNextCursor(): void
    {
        $batch = IdBatch::after([1, 3, 5], 3, 2);

        self::assertSame([5], $batch->ids());
        self::assertTrue($batch->result(0)->isFinished());
    }

    public function testAnExactlyFullLastBatchIsFinished(): void
    {
        self::assertTrue(IdBatch::after([1, 2], 0, 2)->result(0)->isFinished());
    }

    public function testNothingAboveTheCursorIsAnEmptyFinishedBatch(): void
    {
        self::assertTrue(IdBatch::after([1, 2], 2, 10)->result(0)->isFinished());
    }
}
