<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\ProductValues;

use Amiut\ProductSpecs\ProductValues\ProductValueDocument;
use Amiut\ProductSpecs\ProductValues\WordPressProductValueRepository;
use PHPUnit\Framework\TestCase;

final class WordPressProductValueRepositoryTest extends TestCase
{
    private WordPressProductValueRepository $repository;

    protected function setUp(): void
    {
        $GLOBALS['wp_test_post_meta'] = [];
        $GLOBALS['wp_test_posts'] = [];
        $this->repository = new WordPressProductValueRepository();
    }

    public function testReplaceUsesOneVersionedDocumentAndAcceptsANoOp(): void
    {
        $document = ProductValueDocument::fromValues(['screen-a1b2c3d4' => '15']);

        self::assertTrue($this->repository->replace(10, $document));
        self::assertTrue($this->repository->replace(10, $document));
        self::assertSame($document->toStorage(), $this->repository->read(10)->toStorage());
    }

    public function testCountAndPurgePreserveUnrelatedValues(): void
    {
        $GLOBALS['wp_test_posts'] = [10, 11];
        $this->repository->replace(
            10,
            ProductValueDocument::fromValues(['screen-a1b2c3d4' => '15', 'weight-a1b2c3d4' => '2'])
        );
        $this->repository->replace(
            11,
            ProductValueDocument::fromValues(['screen-a1b2c3d4' => '17'])
        );

        self::assertSame(2, $this->repository->countContaining('screen-a1b2c3d4'));
        self::assertTrue($this->repository->purge('screen-a1b2c3d4'));
        self::assertSame(['weight-a1b2c3d4' => '2'], $this->repository->read(10)->values());
        self::assertSame([], $this->repository->read(11)->values());
    }
}
