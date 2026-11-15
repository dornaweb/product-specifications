<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Deletion;

use Amiut\ProductSpecs\Specification\Deletion\BackgroundPurge;
use Amiut\ProductSpecs\Specification\Deletion\PurgeQueue;
use Amiut\ProductSpecs\Specification\Deletion\ValuePurge;
use Amiut\ProductSpecs\Tests\Unit\Support\InMemoryProductValues;
use PHPUnit\Framework\TestCase;

final class BackgroundPurgeTest extends TestCase
{
    private InMemoryProductValues $values;

    protected function setUp(): void
    {
        $GLOBALS['wp_test_options'] = [];
        $GLOBALS['wp_test_scheduled'] = [];
        $this->values = new InMemoryProductValues();
    }

    public function testAChunkAdvancesTheCursorAndTheLastOneRemovesTheKeyFromTheQueue(): void
    {
        for ($postId = 1; $postId <= BackgroundPurge::CHUNK_SIZE + 1; $postId++) {
            $this->values->maps[$postId] = ['weight' => $postId];
        }

        $queue = new PurgeQueue();
        $queue->add('weight', 0);
        $background = $this->background($queue);

        $background->runChunk();
        self::assertSame(BackgroundPurge::CHUNK_SIZE, $queue->entries()[0]->cursor());
        self::assertCount(1, $this->values->maps);

        $background->runChunk();
        self::assertFalse($queue->holds('weight'));
        self::assertSame([], $this->values->maps);
    }

    public function testAFailureIsRecordedAndTheKeyStaysHeld(): void
    {
        $this->values->maps = [1 => ['weight' => 1], 2 => ['weight' => 2]];
        $this->values->failOnPostId = 2;
        $queue = new PurgeQueue();
        $queue->add('weight', 0);

        $this->background($queue)->runChunk();

        self::assertTrue($queue->holds('weight'));
        self::assertNull($queue->pending());
        self::assertSame(1, $queue->entries()[0]->cursor());
        self::assertStringContainsString('product 2', (string) $queue->entries()[0]->failure());
    }

    public function testProcessingQueuesTheNextEventWhileWorkRemains(): void
    {
        $this->values->maps = [1 => ['weight' => 1]];
        $queue = new PurgeQueue();
        $queue->add('weight', 0);

        $this->background($queue)->process();

        self::assertSame([], $this->values->maps);
        self::assertArrayHasKey(BackgroundPurge::HOOK, $GLOBALS['wp_test_scheduled']);
    }

    private function background(PurgeQueue $queue): BackgroundPurge
    {
        return new BackgroundPurge($queue, new ValuePurge($this->values), 60.0);
    }
}
