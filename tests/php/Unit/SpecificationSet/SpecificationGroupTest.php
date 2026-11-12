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
        $group = self::group(101, 102);

        $changed = $group->withItem(SpecificationSetItem::create(103, 'depth'));

        static::assertSame([101, 102, 103], self::termIds($changed));
        static::assertSame([101, 102], self::termIds($group));
    }

    public function testWithItemRefusesATermAlreadyInTheGroup(): void
    {
        $this->expectException(InvalidStructureOperationException::class);

        self::group(101)->withItem(SpecificationSetItem::create(101, 'width'));
    }

    /**
     * @return array<string, array{int, list<int>}>
     */
    public static function itemPositionProvider(): array
    {
        return [
            'to the front' => [0, [103, 101, 102]],
            'to the middle' => [1, [101, 103, 102]],
            'to the back' => [2, [101, 102, 103]],
        ];
    }

    /**
     * @param list<int> $expected
     * @dataProvider itemPositionProvider
     */
    public function testWithItemMovedToPlacesTheItemAndKeepsTheRestInOrder(
        int $position,
        array $expected
    ): void {
        $group = self::group(101, 102, 103);

        static::assertSame($expected, self::termIds($group->withItemMovedTo(103, $position)));
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

        self::group(101, 102, 103)->withItemMovedTo(101, $position);
    }

    public function testWithItemMovedToRefusesATermThatIsNotInTheGroup(): void
    {
        $this->expectException(InvalidStructureOperationException::class);

        self::group(101)->withItemMovedTo(999, 0);
    }

    public function testWithoutItemRemovesOnlyThatItem(): void
    {
        $group = self::group(101, 102, 103);

        static::assertSame([101, 103], self::termIds($group->withoutItem(102)));
        static::assertSame([101, 102, 103], self::termIds($group));
    }

    public function testWithoutItemRefusesATermThatIsNotInTheGroup(): void
    {
        $this->expectException(InvalidStructureOperationException::class);

        self::group(101)->withoutItem(999);
    }

    public function testWithLabelChangesNothingElse(): void
    {
        $changed = self::group(101)->withLabel('Physical');

        static::assertSame('Physical', $changed->label());
        static::assertSame('display', $changed->key());
        static::assertSame('Screen and panel', $changed->description());
        static::assertTrue($changed->visible());
        static::assertSame([101], self::termIds($changed));
    }

    private static function group(int ...$termIds): SpecificationGroup
    {
        return new SpecificationGroup(
            'display',
            'Display',
            'Screen and panel',
            true,
            array_map(
                static fn (int $termId): SpecificationSetItem => SpecificationSetItem::create(
                    $termId,
                    'spec_' . $termId
                ),
                $termIds
            )
        );
    }

    /**
     * @return list<int>
     */
    private static function termIds(SpecificationGroup $group): array
    {
        return array_map(
            static fn (SpecificationSetItem $item): int => $item->termId(),
            $group->items()
        );
    }
}
