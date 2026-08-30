<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\SpecificationSet;

use Amiut\ProductSpecs\SpecificationSet\InvalidStructureOperationException;
use Amiut\ProductSpecs\SpecificationSet\SpecificationGroup;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetItem;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetMapper;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetStructure;
use Amiut\ProductSpecs\SpecificationSet\StructureMetaDefinition;
use PHPUnit\Framework\TestCase;

final class SpecificationSetStructureTest extends TestCase
{
    public function testWithGroupAppendsAndLeavesTheReceiverAlone(): void
    {
        $structure = self::structure('display', 'battery');

        $changed = $structure->withGroup(self::group('ports'));

        static::assertSame(['display', 'battery', 'ports'], self::groupKeys($changed));
        static::assertSame(['display', 'battery'], self::groupKeys($structure));
    }

    public function testWithGroupRefusesAKeyAlreadyInTheSet(): void
    {
        $this->expectException(InvalidStructureOperationException::class);

        self::structure('display')->withGroup(self::group('display'));
    }

    public function testWithGroupLabelChangesOnlyTheLabel(): void
    {
        $changed = self::structure('display', 'battery')
            ->withGroupLabel('display', 'Screen');

        static::assertSame('Screen', $changed->group('display')->label());
        static::assertSame('display', $changed->group('display')->key());
        static::assertSame(['display', 'battery'], self::groupKeys($changed));
    }

    /**
     * @return array<string, array{int, list<string>}>
     */
    public static function groupPositionProvider(): array
    {
        return [
            'to the front' => [0, ['ports', 'display', 'battery']],
            'to the middle' => [1, ['display', 'ports', 'battery']],
            'to the back' => [2, ['display', 'battery', 'ports']],
        ];
    }

    /**
     * @param list<string> $expected
     * @dataProvider groupPositionProvider
     */
    public function testWithGroupMovedToPlacesTheGroupAndKeepsTheRestInOrder(
        int $position,
        array $expected
    ): void {
        $structure = self::structure('display', 'battery', 'ports');

        static::assertSame($expected, self::groupKeys($structure->withGroupMovedTo('ports', $position)));
    }

    /**
     * @return array<string, array{int}>
     */
    public static function outOfRangeGroupPositionProvider(): array
    {
        return [
            'negative' => [-1],
            'one past the end' => [3],
        ];
    }

    /**
     * @dataProvider outOfRangeGroupPositionProvider
     */
    public function testWithGroupMovedToRefusesAPositionOutsideTheList(int $position): void
    {
        $this->expectException(InvalidStructureOperationException::class);

        self::structure('display', 'battery', 'ports')->withGroupMovedTo('display', $position);
    }

    public function testWithoutGroupRemovesTheGroupAndItsItems(): void
    {
        $structure = self::structure('display', 'battery')
            ->withItem('display', SpecificationSetItem::create(101, 'width'));

        $changed = $structure->withoutGroup('display');

        static::assertSame(['battery'], self::groupKeys($changed));
        static::assertFalse($changed->hasGroup('display'));
        static::assertSame(['display', 'battery'], self::groupKeys($structure));
    }

    /**
     * @return array<string, array{callable(SpecificationSetStructure): mixed}>
     */
    public static function unknownGroupOperationProvider(): array
    {
        return [
            'rename' => [static fn (SpecificationSetStructure $s) => $s->withGroupLabel('nope', 'x')],
            'move' => [static fn (SpecificationSetStructure $s) => $s->withGroupMovedTo('nope', 0)],
            'remove' => [static fn (SpecificationSetStructure $s) => $s->withoutGroup('nope')],
            'add item' => [
                static fn (SpecificationSetStructure $s) => $s->withItem(
                    'nope',
                    SpecificationSetItem::create(101, 'width')
                ),
            ],
            'remove item' => [static fn (SpecificationSetStructure $s) => $s->withoutItem('nope', 101)],
            'move item out of' => [
                static fn (SpecificationSetStructure $s) => $s->withItemMovedTo('nope', 101, 'display', 0),
            ],
            'move item into' => [
                static fn (SpecificationSetStructure $s) => $s->withItemMovedTo('display', 101, 'nope', 0),
            ],
        ];
    }

    /**
     * @param callable(SpecificationSetStructure): mixed $operation
     * @dataProvider unknownGroupOperationProvider
     */
    public function testEveryOperationRefusesAnUnknownGroup(callable $operation): void
    {
        $this->expectException(InvalidStructureOperationException::class);

        $operation(
            self::structure('display')->withItem('display', SpecificationSetItem::create(101, 'width'))
        );
    }

    public function testTheSameSpecificationMayAppearInTwoGroups(): void
    {
        $structure = self::structure('display', 'battery')
            ->withItem('display', SpecificationSetItem::create(101, 'width'))
            ->withItem('battery', SpecificationSetItem::create(101, 'width'));

        static::assertSame([101], self::termIds($structure, 'display'));
        static::assertSame([101], self::termIds($structure, 'battery'));
    }

    public function testWithItemMovedToTransfersBetweenGroupsAtTheGivenPosition(): void
    {
        $structure = self::structure('display', 'battery')
            ->withItem('display', SpecificationSetItem::create(101, 'width'))
            ->withItem('battery', SpecificationSetItem::create(201, 'capacity'))
            ->withItem('battery', SpecificationSetItem::create(202, 'life'));

        $changed = $structure->withItemMovedTo('display', 101, 'battery', 1);

        static::assertSame([], self::termIds($changed, 'display'));
        static::assertSame([201, 101, 202], self::termIds($changed, 'battery'));
    }

    public function testWithItemMovedToAcceptsAppendingToTheDestination(): void
    {
        $structure = self::structure('display', 'battery')
            ->withItem('display', SpecificationSetItem::create(101, 'width'))
            ->withItem('battery', SpecificationSetItem::create(201, 'capacity'));

        $changed = $structure->withItemMovedTo('display', 101, 'battery', 1);

        static::assertSame([201, 101], self::termIds($changed, 'battery'));
    }

    public function testWithItemMovedToWithinOneGroupIsAReorder(): void
    {
        $structure = self::structure('display')
            ->withItem('display', SpecificationSetItem::create(101, 'width'))
            ->withItem('display', SpecificationSetItem::create(102, 'height'));

        $changed = $structure->withItemMovedTo('display', 102, 'display', 0);

        static::assertSame([102, 101], self::termIds($changed, 'display'));
    }

    public function testWithItemMovedToRefusesATermMissingFromTheSourceGroup(): void
    {
        $this->expectException(InvalidStructureOperationException::class);

        self::structure('display', 'battery')->withItemMovedTo('display', 999, 'battery', 0);
    }

    public function testWithItemMovedToRefusesATermAlreadyInTheDestination(): void
    {
        $structure = self::structure('display', 'battery')
            ->withItem('display', SpecificationSetItem::create(101, 'width'))
            ->withItem('battery', SpecificationSetItem::create(101, 'width'));

        $this->expectException(InvalidStructureOperationException::class);

        $structure->withItemMovedTo('display', 101, 'battery', 0);
    }

    public function testSchemaVersionSurvivesEveryOperation(): void
    {
        $structure = self::structure('display')
            ->withGroup(self::group('battery'))
            ->withItem('display', SpecificationSetItem::create(101, 'width'))
            ->withItemMovedTo('display', 101, 'battery', 0)
            ->withGroupLabel('battery', 'Power')
            ->withGroupMovedTo('battery', 0)
            ->withoutItem('battery', 101)
            ->withoutGroup('display');

        static::assertSame(StructureMetaDefinition::CURRENT_VERSION, $structure->schemaVersion());
    }

    /**
     * The operations reshape the list that ends up in post meta, so what the
     * mapper produces afterwards is what actually gets stored.
     */
    public function testAnOperatedStructureStillMapsToTheStoredShape(): void
    {
        $structure = self::structure('display')
            ->withGroup(self::group('battery'))
            ->withItem('display', SpecificationSetItem::create(101, 'width'))
            ->withItem('display', SpecificationSetItem::create(102, 'height'))
            ->withItemMovedTo('display', 101, 'battery', 0)
            ->withGroupMovedTo('battery', 0);

        static::assertSame(
            [
                'schema_version' => StructureMetaDefinition::CURRENT_VERSION,
                'groups' => [
                    [
                        'key' => 'battery',
                        'label' => 'Battery',
                        'description' => '',
                        'visible' => true,
                        'items' => [
                            ['term_id' => 101, 'slug' => 'width', 'visible' => true, 'comparable' => false],
                        ],
                    ],
                    [
                        'key' => 'display',
                        'label' => 'Display',
                        'description' => '',
                        'visible' => true,
                        'items' => [
                            ['term_id' => 102, 'slug' => 'height', 'visible' => true, 'comparable' => false],
                        ],
                    ],
                ],
            ],
            (new SpecificationSetMapper())->structureToArray($structure)
        );
    }

    private static function structure(string ...$groupKeys): SpecificationSetStructure
    {
        return new SpecificationSetStructure(
            StructureMetaDefinition::CURRENT_VERSION,
            array_map(static fn (string $key): SpecificationGroup => self::group($key), $groupKeys)
        );
    }

    private static function group(string $key): SpecificationGroup
    {
        return new SpecificationGroup($key, ucfirst($key), '', true, []);
    }

    /**
     * @return list<string>
     */
    private static function groupKeys(SpecificationSetStructure $structure): array
    {
        return array_map(
            static fn (SpecificationGroup $group): string => $group->key(),
            $structure->groups()
        );
    }

    /**
     * @return list<int>
     */
    private static function termIds(SpecificationSetStructure $structure, string $groupKey): array
    {
        return array_map(
            static fn (SpecificationSetItem $item): int => $item->termId(),
            $structure->group($groupKey)->items()
        );
    }
}
