<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Comparison;

use Amiut\ProductSpecs\Comparison\ComparedProductIds;
use PHPUnit\Framework\TestCase;

final class ComparedProductIdsTest extends TestCase
{
    /**
     * @dataProvider queries
     * @param list<int> $expected
     */
    public function testTheQueryKeepsPositiveIdsOnceInTheirOrder(mixed $raw, array $expected): void
    {
        self::assertSame($expected, ComparedProductIds::fromQuery($raw)->ids());
    }

    /**
     * @return array<string, array{mixed, list<int>}>
     */
    public static function queries(): array
    {
        return [
            'ids in order' => ['34,12,56', [34, 12, 56]],
            'spaces around ids' => [' 34 , 12 ', [34, 12]],
            'a repeated id keeps its first place' => ['12,34,12', [12, 34]],
            'junk, zero, negatives, fractions and number prefixes' => ['abc,0,-5,1.5,12abc,,7', [7]],
            'nothing usable' => ['', []],
            'not a string' => [['12'], []],
        ];
    }

    public function testOnlyTheFirstFiftyIdsAreRead(): void
    {
        $ids = ComparedProductIds::fromQuery(implode(',', range(1, 80)))->ids();

        self::assertCount(50, $ids);
        self::assertSame(50, end($ids));
    }

    public function testRemovingBuildsTheLinkQuery(): void
    {
        $ids = ComparedProductIds::fromQuery('12,34');

        self::assertSame('34', $ids->without(12)->toQuery());
        self::assertTrue($ids->without(12)->without(34)->isEmpty());
    }
}
