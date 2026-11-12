<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\ProductValues;

use Amiut\ProductSpecs\ProductValues\ProductValueDocument;
use Amiut\ProductSpecs\ProductValues\WordPressProductValueRepository;
use Amiut\ProductSpecs\Tests\Unit\Support\FakeWpdb;
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

    protected function tearDown(): void
    {
        FakeWpdb::uninstall();
    }

    public function testOnlyProductsUsingOneOfTheValuesAreCounted(): void
    {
        FakeWpdb::install([
            self::stored(['colour-a1b2c3d4' => 'Red']),
            self::stored(['colour-a1b2c3d4' => 'Blue']),
            self::stored(['notes-a1b2c3d4' => 'colour-a1b2c3d4', 'size-a1b2c3d4' => 'Red']),
        ]);

        self::assertSame(1, $this->repository->countUsing('colour-a1b2c3d4', ['Red']));
    }

    public function testTheKeyIsMatchedLiterallyInsideItsSerializedForm(): void
    {
        $wpdb = FakeWpdb::install();

        $this->repository->countUsing('%d8%b1_a1b2c3d4', ['Red']);

        self::assertContains('%s:15:"\\%d8\\%b1\\_a1b2c3d4";%', $wpdb->preparedArguments);
    }

    /**
     * @param array<string, mixed> $values
     */
    private static function stored(array $values): string
    {
        return serialize(ProductValueDocument::fromValues($values)->toStorage());
    }
}
