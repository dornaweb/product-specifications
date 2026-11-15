<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\ImportExport;

use Amiut\ProductSpecs\ImportExport\EntryOutcome;
use Amiut\ProductSpecs\ImportExport\ImportPlanner;
use Amiut\ProductSpecs\ImportExport\Package;
use PHPUnit\Framework\TestCase;

final class ImportPlannerTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['wp_test_terms'] = [];
        $GLOBALS['wp_test_term_meta'] = [];
        $GLOBALS['wp_test_posts'] = [];

        Sections::storedSpecification(10, 'battery', 'Battery', 'text');
    }

    public function testSetsArePlannedAfterSpecificationsAndLeaveOutWhatWillNotExist(): void
    {
        $package = new Package([
            'sets' => [
                [
                    'slug' => 'phones',
                    'title' => 'Phones',
                    'groups' => [
                        [
                            'key' => 'general',
                            'label' => 'General',
                            'items' => [
                                ['specification' => 'weight'],
                                ['specification' => 'battery'],
                                ['specification' => 'ghost'],
                            ],
                        ],
                    ],
                ],
            ],
            'specifications' => [
                ['slug' => 'weight', 'name' => 'Weight', 'type' => 'text'],
                ['slug' => 'battery', 'name' => 'Battery', 'type' => 'true_false'],
            ],
            'colours' => [['anything' => true]],
        ]);

        $plan = (new ImportPlanner(Sections::registry(new InMemorySetRepository())))->plan($package);
        $set = $plan->outcome('sets', 0);

        self::assertSame(EntryOutcome::CREATE, $plan->outcome('specifications', 0)?->status());
        self::assertSame(EntryOutcome::CONFLICT, $plan->outcome('specifications', 1)?->status());
        self::assertSame(EntryOutcome::CREATE, $set?->status());
        self::assertSame(
            [
                'Leaves out "battery": the file\'s specification was not imported.',
                'Leaves out "ghost": no specification has that slug in the file or on this store.',
            ],
            $set?->notes()
        );
        self::assertSame(['colours'], $plan->unknownSections());
    }
}
