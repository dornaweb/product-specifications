<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\SpecificationSet;

use Amiut\ProductSpecs\SpecificationSet\SpecificationSetMapper;
use Amiut\ProductSpecs\SpecificationSet\StructureMetaDefinition;
use PHPUnit\Framework\TestCase;

/**
 * The mapper decides what reaches `specification_set_structure`, so the shape it
 * produces is a storage contract, not an implementation detail.
 */
final class SpecificationSetMapperTest extends TestCase
{
    private SpecificationSetMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new SpecificationSetMapper();
    }

    public function testStoredStructureSurvivesARoundTrip(): void
    {
        $stored = [
            'schema_version' => 1,
            'groups' => [
                [
                    'key' => 'display',
                    'label' => 'Display',
                    'description' => 'Screen and panel',
                    'visible' => true,
                    'items' => [
                        ['term_id' => 123, 'slug' => 'screen_size', 'visible' => true],
                        ['term_id' => 124, 'slug' => 'resolution', 'visible' => false],
                    ],
                ],
                [
                    'key' => 'battery',
                    'label' => 'Battery',
                    'description' => '',
                    'visible' => false,
                    'items' => [],
                ],
            ],
        ];

        static::assertSame(
            $stored,
            $this->mapper->structureToArray($this->mapper->structureFromArray($stored))
        );
    }

    public function testMissingFlagsTakeTheDocumentedDefaults(): void
    {
        $structure = $this->mapper->structureFromArray([
            'schema_version' => 1,
            'groups' => [
                [
                    'key' => 'display',
                    'label' => 'Display',
                    'items' => [
                        ['term_id' => 123, 'slug' => 'screen_size'],
                    ],
                ],
            ],
        ]);

        static::assertSame(
            [
                'schema_version' => 1,
                'groups' => [
                    [
                        'key' => 'display',
                        'label' => 'Display',
                        'description' => '',
                        'visible' => true,
                        'items' => [
                            ['term_id' => 123, 'slug' => 'screen_size', 'visible' => true],
                        ],
                    ],
                ],
            ],
            $this->mapper->structureToArray($structure)
        );
    }

    /**
     * The mapper trusts what reaches it, because StructureSanitizer has already
     * run. It still guards types, since a non-array entry would be a TypeError
     * rather than a bad value.
     */
    public function testAnEntryOfTheWrongTypeIsSkipped(): void
    {
        $structure = $this->mapper->structureFromArray([
            'schema_version' => 1,
            'groups' => [
                'not a group',
                ['key' => 'display', 'label' => 'Display', 'items' => ['not an item']],
            ],
        ]);

        static::assertCount(1, $structure->groups());
        static::assertSame([], $structure->groups()[0]->items());
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function unusableStructureProvider(): array
    {
        return [
            'null meta' => [null],
            'string meta' => ['broken'],
            'groups not an array' => [['schema_version' => 1, 'groups' => 'broken']],
            'group not an array' => [['schema_version' => 1, 'groups' => ['broken']]],
        ];
    }

    /**
     * @dataProvider unusableStructureProvider
     */
    public function testUnusableStructureBecomesAnEmptyOne(mixed $stored): void
    {
        $structure = $this->mapper->structureFromArray($stored);

        static::assertSame([], $structure->groups());
        static::assertSame(StructureMetaDefinition::CURRENT_VERSION, $structure->schemaVersion());
    }
}
