<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\SpecificationSet;

use Amiut\ProductSpecs\SpecificationSet\InvalidStructureOperationException;
use Amiut\ProductSpecs\SpecificationSet\SpecificationGroup;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetItem;
use PHPUnit\Framework\TestCase;

final class SpecificationGroupTest extends TestCase
{
    public function testWithItemAppendsAndLeavesTheReceiverAlone(): void
    {
        $group = self::group('a', 'b');

        $changed = $group->withItem(SpecificationSetItem::create('c'));

        static::assertSame(['a', 'b', 'c'], self::slugs($changed));
        static::assertSame(['a', 'b'], self::slugs($group));
    }

    public function testWithItemRefusesASpecificationAlreadyInTheGroup(): void
    {
        $this->expectException(InvalidStructureOperationException::class);

        self::group('a')->withItem(SpecificationSetItem::create('a'));
    }

    /**
     * @return array<string, array{int, list<string>}>
     */
    public static function itemPositionProvider(): array
    {
        return [
            'to the front' => [0, ['c', 'a', 'b']],
            'to the middle' => [1, ['a', 'c', 'b']],
            'to the back' => [2, ['a', 'b', 'c']],
        ];
    }

    /**
     * @param list<string> $expected
     * @dataProvider itemPositionProvider
     */
    public function testWithItemMovedToPlacesTheItemAndKeepsTheRestInOrder(
        int $position,
        array $expected
    ): void {
        $group = self::group('a', 'b', 'c');

        static::assertSame($expected, self::slugs($group->withItemMovedTo('c', $position)));
    }

    /**
     * @return array<string, array{int}>
     */
    public static function outOfRangeItemPositionProvider(): array
    {
        return [
            'negative' => [-1],
            'one past the end' => [3],
        ];
    }

    /**
     * @dataProvider outOfRangeItemPositionProvider
     */
    public function testWithItemMovedToRefusesAPositionOutsideTheList(int $position): void
    {
        $this->expectException(InvalidStructureOperationException::class);

        self::group('a', 'b', 'c')->withItemMovedTo('a', $position);
    }

    public function testWithItemMovedToRefusesASpecificationThatIsNotInTheGroup(): void
    {
        $this->expectException(InvalidStructureOperationException::class);

        self::group('a')->withItemMovedTo('missing', 0);
    }

    public function testWithoutItemRemovesOnlyThatItem(): void
    {
        $group = self::group('a', 'b', 'c');

        static::assertSame(['a', 'c'], self::slugs($group->withoutItem('b')));
        static::assertSame(['a', 'b', 'c'], self::slugs($group));
    }

    public function testWithoutItemRefusesASpecificationThatIsNotInTheGroup(): void
    {
        $this->expectException(InvalidStructureOperationException::class);

        self::group('a')->withoutItem('missing');
    }

    public function testWithLabelChangesNothingElse(): void
    {
        $changed = self::group('a')->withLabel('Physical');

        static::assertSame('Physical', $changed->label());
        static::assertSame('display', $changed->key());
        static::assertSame('Screen and panel', $changed->description());
        static::assertTrue($changed->visible());
        static::assertSame(['a'], self::slugs($changed));
    }

    private static function group(string ...$slugs): SpecificationGroup
    {
        return new SpecificationGroup(
            'display',
            'Display',
            'Screen and panel',
            true,
            array_map(static fn (string $slug): SpecificationSetItem => SpecificationSetItem::create($slug), $slugs)
        );
    }

    /**
     * @return list<string>
     */
    private static function slugs(SpecificationGroup $group): array
    {
        return array_map(static fn (SpecificationSetItem $item): string => $item->slug(), $group->items());
    }
}
