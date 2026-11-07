<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\SpecificationSet;

use Amiut\ProductSpecs\SpecificationSet\StructureMetaDefinition;
use Amiut\ProductSpecs\SpecificationSet\StructureSanitizer;
use PHPUnit\Framework\TestCase;

/**
 * The sanitizer is where the documented defaults actually come from. WordPress
 * applies schema defaults to top-level endpoint arguments, not to properties
 * nested inside an object, so the `default` entries in StructureMetaDefinition
 * never fire on their own.
 */
final class StructureSanitizerTest extends TestCase
{
    private StructureSanitizer $sanitizer;

    protected function setUp(): void
    {
        $this->sanitizer = new StructureSanitizer();
    }

    public function testMissingFlagsGetTheDocumentedDefaults(): void
    {
        $sanitized = $this->sanitizer->sanitize([
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

        $group = $sanitized['groups'][0];

        static::assertTrue($group['visible']);
        static::assertSame('', $group['description']);
        static::assertTrue($group['items'][0]['visible']);
        static::assertFalse($group['items'][0]['comparable']);
    }

    public function testAPercentEncodedSlugForANonLatinNameSurvives(): void
    {
        $sanitized = $this->sanitizer->sanitize([
            'groups' => [
                [
                    'key' => 'display',
                    'label' => 'Display',
                    'items' => [
                        ['term_id' => 123, 'slug' => '%d8%b1%d9%86%da%af'],
                    ],
                ],
            ],
        ]);

        static::assertSame('%d8%b1%d9%86%da%af', $sanitized['groups'][0]['items'][0]['slug']);
    }

    public function testExplicitFalseSurvivesInsteadOfBeingDefaulted(): void
    {
        $sanitized = $this->sanitizer->sanitize([
            'groups' => [
                [
                    'key' => 'display',
                    'label' => 'Display',
                    'visible' => false,
                    'items' => [
                        ['term_id' => 123, 'slug' => 'screen_size', 'visible' => false],
                    ],
                ],
            ],
        ]);

        static::assertFalse($sanitized['groups'][0]['visible']);
        static::assertFalse($sanitized['groups'][0]['items'][0]['visible']);
    }

    /**
     * The mapper reads these arrays back by position as well as by key, so key
     * order is part of the stored shape, not a formatting detail.
     */
    public function testKeyOrderIsCanonical(): void
    {
        $sanitized = $this->sanitizer->sanitize([
            'groups' => [
                [
                    'items' => [
                        ['comparable' => true, 'slug' => 'screen_size', 'visible' => true, 'term_id' => 123],
                    ],
                    'visible' => true,
                    'label' => 'Display',
                    'description' => '',
                    'key' => 'display',
                ],
            ],
        ]);

        static::assertSame(['schema_version', 'groups'], array_keys($sanitized));
        static::assertSame(
            ['key', 'label', 'description', 'visible', 'items'],
            array_keys($sanitized['groups'][0])
        );
        static::assertSame(
            ['term_id', 'slug', 'visible', 'comparable'],
            array_keys($sanitized['groups'][0]['items'][0])
        );
    }

    public function testSchemaVersionIsAlwaysTheCurrentOne(): void
    {
        $sanitized = $this->sanitizer->sanitize(['schema_version' => 99, 'groups' => []]);

        static::assertSame(StructureMetaDefinition::CURRENT_VERSION, $sanitized['schema_version']);
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function unusableItemProvider(): array
    {
        return [
            'not an array' => ['screen_size'],
            'term id below one' => [['term_id' => 0, 'slug' => 'screen_size']],
            'term id missing' => [['slug' => 'screen_size']],
            'slug empty' => [['term_id' => 123, 'slug' => '']],
            'slug missing' => [['term_id' => 123]],
        ];
    }

    /**
     * A direct update_post_meta() call never passes through the REST schema, so
     * an item with no resolvable term still has to be stopped here.
     *
     * @dataProvider unusableItemProvider
     */
    public function testUnusableItemIsDropped(mixed $item): void
    {
        $sanitized = $this->sanitizer->sanitize([
            'groups' => [
                ['key' => 'display', 'label' => 'Display', 'items' => [$item]],
            ],
        ]);

        static::assertSame([], $sanitized['groups'][0]['items']);
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function unusableGroupProvider(): array
    {
        return [
            'not an array' => ['display'],
            'key missing' => [['label' => 'Display', 'items' => []]],
            'key empty' => [['key' => '', 'label' => 'Display', 'items' => []]],
            'label missing' => [['key' => 'display', 'items' => []]],
            'label empty' => [['key' => 'display', 'label' => '', 'items' => []]],
        ];
    }

    /**
     * @dataProvider unusableGroupProvider
     */
    public function testUnusableGroupIsDropped(mixed $group): void
    {
        static::assertSame([], $this->sanitizer->sanitize(['groups' => [$group]])['groups']);
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function unusableStructureProvider(): array
    {
        return [
            'null meta' => [null],
            'string meta' => ['broken'],
            'groups not an array' => [['groups' => 'broken']],
        ];
    }

    /**
     * @dataProvider unusableStructureProvider
     */
    public function testUnusableStructureBecomesAnEmptyOne(mixed $stored): void
    {
        static::assertSame(
            StructureMetaDefinition::default(),
            $this->sanitizer->sanitize($stored)
        );
    }
}
