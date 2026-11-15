<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\ImportExport;

use Amiut\ProductSpecs\ImportExport\EntryOutcome;
use Amiut\ProductSpecs\ImportExport\ImportPlanner;
use Amiut\ProductSpecs\ImportExport\InvalidPackage;
use Amiut\ProductSpecs\ImportExport\PackageReader;
use PHPUnit\Framework\TestCase;

final class PackageReaderTest extends TestCase
{
    /**
     * @dataProvider rejectedFiles
     */
    public function testAFileThatIsNotAPackageIsRejectedWhole(string $json, string $error): void
    {
        self::assertSame([$error], $this->errors($json));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function rejectedFiles(): array
    {
        return [
            'not JSON' => ['{"format":', 'The file is not valid JSON.'],
            'another format' => ['{"format":"other"}', 'The file is not a Product Specifications package.'],
            'a schema version that is not a number' => [
                '{"format":"product-specifications","schema_version":"1","sections":{}}',
                'schema_version: must be a positive whole number',
            ],
            'a newer schema version' => [
                '{"format":"product-specifications","schema_version":2,"sections":{}}',
                'The file uses package schema 2, made by a newer version of Product Specifications. This site reads up to schema 1. Update the plugin and try again.',
            ],
            'sections as a list' => [
                '{"format":"product-specifications","schema_version":1,"sections":[1]}',
                'sections: must be an object',
            ],
        ];
    }

    public function testEveryMalformedEntryKeepsItsProblemsAndTheRestOfTheFileIsRead(): void
    {
        $package = $this->reader()->read($this->package([
            'specifications' => [
                ['slug' => 'Bad Slug', 'name' => '', 'type' => 'text'],
                ['slug' => 'weight', 'name' => 'Weight', 'type' => 'text'],
                ['slug' => 'weight', 'name' => 'Mass', 'type' => 'text'],
                'not an object',
            ],
            'sets' => [
                [
                    'slug' => 'phones',
                    'title' => 'Phones',
                    'status' => 'archived',
                    'groups' => [],
                ],
                [
                    'slug' => 'tablets',
                    'title' => 'Tablets',
                    'groups' => [
                        [
                            'key' => 'general',
                            'label' => 'General',
                            'items' => [['specification' => 'weight'], [], ['specification' => 'weight']],
                        ],
                        ['key' => 'general', 'label' => 'Again', 'items' => [], 'visible' => 'yes'],
                    ],
                ],
                ['slug' => 'laptops', 'title' => 'Laptops', 'status' => 'draft', 'groups' => []],
            ],
        ]));

        self::assertSame(
            [
                '[0].slug: may contain only lowercase letters, digits, dashes and underscores',
                '[0].name: must not be empty',
            ],
            $package->problems('specifications', 0)
        );
        self::assertSame([], $package->problems('specifications', 1));
        self::assertSame(['[2].slug: is a duplicate'], $package->problems('specifications', 2));
        self::assertSame(['[3]: must be an object'], $package->problems('specifications', 3));
        self::assertSame([], $package->entries('specifications')[3]);
        self::assertSame(
            ['[0].status: must be one of: publish, draft, pending, private'],
            $package->problems('sets', 0)
        );
        self::assertSame(
            [
                '[1].groups[0].items[1].specification: is required',
                '[1].groups[0].items[2].specification: is a duplicate',
                '[1].groups[1].visible: must be true or false',
            ],
            $package->problems('sets', 1)
        );
        self::assertSame([], $package->problems('sets', 2));
    }

    public function testAnEntryWithProblemsIsPlannedAsSkippedWithThem(): void
    {
        $GLOBALS['wp_test_terms'] = [];
        $GLOBALS['wp_test_term_meta'] = [];
        $GLOBALS['wp_test_posts'] = [];
        $registry = Sections::registry(new InMemorySetRepository());

        $plan = (new ImportPlanner($registry))->plan((new PackageReader($registry))->read($this->package([
            'specifications' => [
                ['slug' => 'weight', 'name' => '', 'type' => 'text'],
                ['slug' => 'height', 'name' => 'Height', 'type' => 'text'],
                ['name' => 'Nameless'],
            ],
        ])));

        self::assertSame(
            ['status' => EntryOutcome::SKIP, 'label' => 'weight', 'notes' => ['[0].name: must not be empty']],
            $plan->outcome('specifications', 0)?->toArray()
        );
        self::assertSame(EntryOutcome::CREATE, $plan->outcome('specifications', 1)?->status());
        self::assertSame('[2]', $plan->outcome('specifications', 2)?->label());
    }

    public function testAValidPackageIsReadAndAnUnknownSectionIsKept(): void
    {
        $package = $this->reader()->read($this->package([
            'specifications' => [['slug' => '%d9%88%d8%b2%d9%86', 'name' => 'وزن', 'type' => 'text']],
            'colours' => [['anything' => true]],
        ]));

        self::assertSame(['specifications', 'colours'], $package->sectionKeys());
        self::assertSame('وزن', $package->entries('specifications')[0]['name']);
    }

    /**
     * @param array<string, mixed> $sections
     */
    private function package(array $sections): string
    {
        // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode -- No WordPress in unit tests.
        return (string) json_encode([
            'format' => 'product-specifications',
            'schema_version' => 1,
            'sections' => $sections,
        ]);
    }

    /**
     * @return list<string>
     */
    private function errors(string $json): array
    {
        try {
            $this->reader()->read($json);
        } catch (InvalidPackage $invalid) {
            return $invalid->errors();
        }

        return [];
    }

    private function reader(): PackageReader
    {
        return new PackageReader(Sections::registry(new InMemorySetRepository()));
    }
}
