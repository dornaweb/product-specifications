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
            ->withItem('display', SpecificationSetItem::create('width'));

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
                    SpecificationSetItem::create('width')
                ),
            ],
            'remove item' => [static fn (SpecificationSetStructure $s) => $s->withoutItem('nope', 'width')],
            'move item out of' => [
                static fn (SpecificationSetStructure $s) => $s->withItemMovedTo('nope', 'width', 'display', 0),
            ],
            'move item into' => [
                static fn (SpecificationSetStructure $s) => $s->withItemMovedTo('display', 'width', 'nope', 0),
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
            self::structure('display')->withItem('display', SpecificationSetItem::create('width'))
        );
    }

    public function testTheSameSpecificationMayAppearInTwoGroups(): void
    {
        $structure = self::structure('display', 'battery')
            ->withItem('display', SpecificationSetItem::create('width'))
            ->withItem('battery', SpecificationSetItem::create('width'));

        static::assertSame(['width'], self::slugs($structure, 'display'));
        static::assertSame(['width'], self::slugs($structure, 'battery'));
    }

    public function testWithItemMovedToTransfersBetweenGroupsAtTheGivenPosition(): void
    {
        $structure = self::structure('display', 'battery')
            ->withItem('display', SpecificationSetItem::create('width'))
            ->withItem('battery', SpecificationSetItem::create('capacity'))
            ->withItem('battery', SpecificationSetItem::create('life'));

        $changed = $structure->withItemMovedTo('display', 'width', 'battery', 1);

        static::assertSame([], self::slugs($changed, 'display'));
        static::assertSame(['capacity', 'width', 'life'], self::slugs($changed, 'battery'));
    }

    public function testWithItemMovedToAcceptsAppendingToTheDestination(): void
    {
        $structure = self::structure('display', 'battery')
            ->withItem('display', SpecificationSetItem::create('width'))
            ->withItem('battery', SpecificationSetItem::create('capacity'));

        $changed = $structure->withItemMovedTo('display', 'width', 'battery', 1);

        static::assertSame(['capacity', 'width'], self::slugs($changed, 'battery'));
    }

    public function testWithItemMovedToWithinOneGroupIsAReorder(): void
    {
        $structure = self::structure('display')
            ->withItem('display', SpecificationSetItem::create('width'))
            ->withItem('display', SpecificationSetItem::create('height'));

        $changed = $structure->withItemMovedTo('display', 'height', 'display', 0);

        static::assertSame(['height', 'width'], self::slugs($changed, 'display'));
    }

    public function testWithItemMovedToRefusesASpecificationMissingFromTheSourceGroup(): void
    {
        $this->expectException(InvalidStructureOperationException::class);

        self::structure('display', 'battery')->withItemMovedTo('display', 'missing', 'battery', 0);
    }

    public function testWithItemMovedToRefusesASpecificationAlreadyInTheDestination(): void
    {
        $structure = self::structure('display', 'battery')
            ->withItem('display', SpecificationSetItem::create('width'))
            ->withItem('battery', SpecificationSetItem::create('width'));

        $this->expectException(InvalidStructureOperationException::class);

        $structure->withItemMovedTo('display', 'width', 'battery', 0);
    }

    public function testSchemaVersionSurvivesEveryOperation(): void
    {
        $structure = self::structure('display')
            ->withGroup(self::group('battery'))
            ->withItem('display', SpecificationSetItem::create('width'))
            ->withItemMovedTo('display', 'width', 'battery', 0)
            ->withGroupLabel('battery', 'Power')
            ->withGroupMovedTo('battery', 0)
            ->withoutItem('battery', 'width')
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
            ->withItem('display', SpecificationSetItem::create('width'))
            ->withItem('display', SpecificationSetItem::create('height'))
            ->withItemMovedTo('display', 'width', 'battery', 0)
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
                            ['slug' => 'width', 'visible' => true],
                        ],
                    ],
                    [
                        'key' => 'display',
                        'label' => 'Display',
                        'description' => '',
                        'visible' => true,
                        'items' => [
                            ['slug' => 'height', 'visible' => true],
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
     * @return list<string>
     */
    private static function slugs(SpecificationSetStructure $structure, string $groupKey): array
    {
        return array_map(
            static fn (SpecificationSetItem $item): string => $item->slug(),
            $structure->group($groupKey)->items()
        );
    }

    public function testMergingOnlyAddsAndKeepsWhatIsAlreadyArranged(): void
    {
        $current = new SpecificationSetStructure(1, [
            new SpecificationGroup('general', 'General', '', true, [
                SpecificationSetItem::create('weight'),
                SpecificationSetItem::create('colour'),
            ]),
            new SpecificationGroup('power', 'Power', '', true, [SpecificationSetItem::create('battery')]),
        ]);
        $incoming = new SpecificationSetStructure(1, [
            new SpecificationGroup('display', 'Display', '', true, [SpecificationSetItem::create('screen')]),
            new SpecificationGroup('general', 'Overview', 'Basics', true, [
                SpecificationSetItem::create('battery'),
                SpecificationSetItem::create('material'),
            ]),
        ]);

        $merged = $current->mergedWith($incoming);

        self::assertSame(
            [
                'general' => ['Overview', 'Basics', ['weight', 'colour', 'material']],
                'power' => ['Power', '', ['battery']],
                'display' => ['Display', '', ['screen']],
            ],
            array_combine(
                array_map(static fn (SpecificationGroup $group): string => $group->key(), $merged->groups()),
                array_map(
                    static fn (SpecificationGroup $group): array => [
                        $group->label(),
                        $group->description(),
                        array_map(static fn (SpecificationSetItem $item): string => $item->slug(), $group->items()),
                    ],
                    $merged->groups()
                )
            )
        );
    }
}
