<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\ProductValues;

use Amiut\ProductSpecs\ProductValues\ProductValueDocument;
use PHPUnit\Framework\TestCase;

final class ProductValueDocumentTest extends TestCase
{
    public function testItNormalizesAndVersionsAStoredMap(): void
    {
        $document = ProductValueDocument::fromStorage(
            [
                'schema_version' => 99,
                'values' => ['screen-size-a1b2c3d4' => '15', 12 => 'ignored'],
            ]
        );

        self::assertSame(
            [
                'schema_version' => 1,
                'values' => ['screen-size-a1b2c3d4' => '15'],
            ],
            $document->toStorage()
        );
    }

    public function testNullIsPresentUntilTheKeyIsRemoved(): void
    {
        $document = ProductValueDocument::fromValues(['warranty-a1b2c3d4' => null]);

        self::assertTrue($document->has('warranty-a1b2c3d4'));
        self::assertFalse($document->without('warranty-a1b2c3d4')->has('warranty-a1b2c3d4'));
    }

    /**
     * @dataProvider malformedDocuments
     */
    public function testMalformedStorageReadsAsEmpty(mixed $stored): void
    {
        self::assertSame([], ProductValueDocument::fromStorage($stored)->values());
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public function malformedDocuments(): iterable
    {
        yield 'missing' => [''];
        yield 'bare map' => [['key' => 'value']];
        yield 'invalid values' => [['schema_version' => 1, 'values' => 'bad']];
    }
}
