<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Migration;

use Amiut\ProductSpecs\Migration\ChunkResult;
use Amiut\ProductSpecs\Migration\MigrationRun;
use Amiut\ProductSpecs\Migration\MigrationRunRepository;
use PHPUnit\Framework\TestCase;

final class MigrationRunRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['wp_test_options'] = [];
    }

    public function testAStoredRunReadsBackUnchanged(): void
    {
        $repository = new MigrationRunRepository();
        $run = MigrationRun::started('values', 5, 1730000000)
            ->withChunk(new ChunkResult(2, 2, [1], [2 => 'broken']))
            ->failed('database went away');

        $repository->store($run);

        self::assertEquals($run, $repository->stored());
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function malformedProvider(): array
    {
        return [
            'nothing stored' => [null],
            'a scalar' => ['running'],
            'an unknown status' => [['status' => 'paused']],
        ];
    }

    /**
     * @dataProvider malformedProvider
     */
    public function testAMalformedOptionReadsAsIdle(mixed $stored): void
    {
        if ($stored !== null) {
            $GLOBALS['wp_test_options'][MigrationRunRepository::OPTION] = $stored;
        }

        self::assertSame(MigrationRun::IDLE, (new MigrationRunRepository())->stored()->status());
    }
}
