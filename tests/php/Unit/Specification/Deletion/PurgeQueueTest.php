<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Deletion;

use Amiut\ProductSpecs\Specification\Deletion\PurgeQueue;
use PHPUnit\Framework\TestCase;

final class PurgeQueueTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['wp_test_options'] = [];
    }

    public function testAQueuedKeyIsHeldUntilItIsRemovedAndTheOptionGoesWithTheLastOne(): void
    {
        $queue = new PurgeQueue();
        $queue->add('weight', 200);
        $queue->add('colour', 0);

        self::assertTrue($queue->holds('weight'));
        self::assertSame('weight', $queue->pending()?->slug());

        $queue->remove('weight');
        self::assertFalse($queue->holds('weight'));
        self::assertSame('colour', $queue->pending()?->slug());

        $queue->remove('colour');
        self::assertArrayNotHasKey(PurgeQueue::OPTION, $GLOBALS['wp_test_options']);
    }

    public function testAFailedKeyStaysHeldButIsNoLongerPendingUntilRetried(): void
    {
        $queue = new PurgeQueue();
        $queue->add('weight', 0);
        $queue->recordFailure('weight', 'disk full');

        self::assertTrue($queue->holds('weight'));
        self::assertNull($queue->pending());
        self::assertSame('disk full', $queue->entries()[0]->failure());

        $queue->retryAll();

        self::assertSame('weight', $queue->pending()?->slug());
    }

    public function testAdvancingMovesTheCursorAndAddingAgainRestartsIt(): void
    {
        $queue = new PurgeQueue();
        $queue->add('weight', 0);
        $queue->advance('weight', 300);

        self::assertSame(300, $queue->entries()[0]->cursor());

        $queue->add('weight', 50);

        self::assertCount(1, $queue->entries());
        self::assertSame(50, $queue->entries()[0]->cursor());
    }

    public function testUnreadableStoredEntriesAreIgnored(): void
    {
        $GLOBALS['wp_test_options'][PurgeQueue::OPTION] = [
            'junk',
            ['slug' => ''],
            ['slug' => 'weight', 'cursor' => '9'],
        ];

        $entries = (new PurgeQueue())->entries();

        self::assertCount(1, $entries);
        self::assertSame(['weight', 0], [$entries[0]->slug(), $entries[0]->cursor()]);
    }
}
