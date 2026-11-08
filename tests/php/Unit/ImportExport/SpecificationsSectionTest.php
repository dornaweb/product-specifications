<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\ImportExport;

use Amiut\ProductSpecs\ImportExport\EntryOutcome;
use Amiut\ProductSpecs\ImportExport\ImportPlan;
use Amiut\ProductSpecs\Tests\Unit\Support\FakeWpdb;
use PHPUnit\Framework\TestCase;

final class SpecificationsSectionTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['wp_test_terms'] = [];
        $GLOBALS['wp_test_term_meta'] = [];
        $GLOBALS['wp_test_posts'] = [];
        $GLOBALS['wp_test_post_meta'] = [];

        Sections::storedSpecification(10, 'weight', 'Weight', 'text');
        Sections::storedSpecification(11, 'colour', 'Colour', 'select', ['Red', 'Blue', 'Green']);
    }

    /**
     * @dataProvider plannedEntries
     * @param array<string, mixed> $entry
     * @param list<string> $notes
     */
    public function testAnEntryIsPlannedAgainstWhatTheStoreHolds(array $entry, string $status, array $notes): void
    {
        $outcome = Sections::specifications()->planEntry($entry, new ImportPlan());

        self::assertSame([$status, $notes], [$outcome->status(), $outcome->notes()]);
    }

    /**
     * @return array<string, array{array<string, mixed>, string, list<string>}>
     */
    public static function plannedEntries(): array
    {
        return [
            'a new slug' => [
                ['slug' => 'battery', 'name' => 'Battery', 'type' => 'text'],
                EntryOutcome::CREATE,
                [],
            ],
            'the same definition' => [
                ['slug' => 'weight', 'name' => 'Weight', 'type' => 'text', 'description' => ''],
                EntryOutcome::UNCHANGED,
                [],
            ],
            'a renamed specification of the same type' => [
                ['slug' => 'weight', 'name' => 'Battery', 'type' => 'text'],
                EntryOutcome::UPDATE,
                ['Renamed from "Weight" to "Battery".'],
            ],
            'a different type' => [
                ['slug' => 'weight', 'name' => 'Weight', 'type' => 'true_false'],
                EntryOutcome::CONFLICT,
                [
                    'On this store it is a "text" specification, and the file has "true_false". It is left as it is, and the file\'s set items and values for it are skipped.',
                ],
            ],
            'a type this store does not have' => [
                ['slug' => 'size', 'name' => 'Size', 'type' => 'unit'],
                EntryOutcome::SKIP,
                ['The type "unit" is not available on this store.'],
            ],
        ];
    }

    public function testRemovedChoicesThatProductsStillUseAreCounted(): void
    {
        FakeWpdb::install(array_map(
            static fn (string $colour): string => serialize(['schema_version' => 1, 'values' => ['colour' => $colour]]),
            ['Red', 'Green', 'Blue']
        ));

        $outcome = Sections::specifications()->planEntry(
            ['slug' => 'colour', 'name' => 'Colour', 'type' => 'select', 'options' => ['values' => ['Blue', 'Black']]],
            new ImportPlan()
        );

        self::assertSame(EntryOutcome::UPDATE, $outcome->status());
        self::assertSame(['2 products use choices the file removes: Red, Green.'], $outcome->notes());
    }

    protected function tearDown(): void
    {
        FakeWpdb::uninstall();
    }
}
