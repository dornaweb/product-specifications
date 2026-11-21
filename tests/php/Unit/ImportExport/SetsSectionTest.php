<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\ImportExport;

use Amiut\ProductSpecs\ImportExport\EntryOutcome;
use Amiut\ProductSpecs\ImportExport\ImportPlan;
use Amiut\ProductSpecs\ImportExport\Section\SetsSection;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionMapper;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\SpecificationSet\SpecificationGroup;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSet;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetItem;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetStructure;
use PHPUnit\Framework\TestCase;

final class SetsSectionTest extends TestCase
{
    public function testWhatTheSectionExportsItAlsoAccepts(): void
    {
        $section = new SetsSection(
            new InMemorySetRepository([
                'phones' => SpecificationSet::fromStorage(
                    1,
                    'Phones',
                    'publish',
                    new SpecificationSetStructure(1, [
                        new SpecificationGroup('general', 'General', '', false, [
                            SpecificationSetItem::create('%d9%88%d8%b2%d9%86', false),
                        ]),
                    ]),
                    'phones',
                    'Mobile phones'
                ),
                'untitled' => SpecificationSet::fromStorage(
                    2,
                    '',
                    'draft',
                    SpecificationSetStructure::empty(),
                    'untitled'
                ),
            ]),
            new SpecificationDefinitionRepository(new SpecificationDefinitionMapper())
        );

        $exported = [];

        foreach ($section->export() as $entry) {
            $exported[] = $entry;
        }

        self::assertCount(2, $exported);
        self::assertSame([], $section->validate($exported));
    }

    public function testASetImportedFromItsOwnExportIsPlannedAsUnchanged(): void
    {
        $section = $this->phonesSection();
        $plan = new ImportPlan();
        $entry = [...$section->export()][0];

        self::assertSame(EntryOutcome::UNCHANGED, $section->planEntry($entry, $plan)->status());

        $retitled = ['title' => 'Mobile phones'] + $entry;
        self::assertSame(EntryOutcome::UPDATE, $section->planEntry($retitled, $plan)->status());

        $regrouped = $entry;
        $regrouped['groups'][] = ['key' => 'camera', 'label' => 'Camera', 'items' => []];
        self::assertSame(EntryOutcome::UPDATE, $section->planEntry($regrouped, $plan)->status());
    }

    public function testComparisonTravelsWithTheSetAndAnEntryWithoutItKeepsTheCurrentValue(): void
    {
        $repository = $this->phonesRepository();
        $section = new SetsSection(
            $repository,
            new SpecificationDefinitionRepository(new SpecificationDefinitionMapper())
        );
        $plan = new ImportPlan();
        $entry = [...$section->export()][0];

        self::assertFalse($entry['comparison']);

        $turnedOn = ['comparison' => true] + $entry;
        self::assertSame(EntryOutcome::UPDATE, $section->planEntry($turnedOn, $plan)->status());

        $section->importEntry($turnedOn, $plan);
        unset($entry['comparison']);
        $section->importEntry($entry, $plan);

        self::assertTrue($repository->saved[0]->offersComparison());
        self::assertFalse($repository->saved[1]->offersComparison());
    }

    public function testASetTheImportCreatesOffersComparisonUnlessTheEntrySaysOtherwise(): void
    {
        $repository = new InMemorySetRepository();
        $section = new SetsSection(
            $repository,
            new SpecificationDefinitionRepository(new SpecificationDefinitionMapper())
        );
        $entry = ['slug' => 'tablets', 'title' => 'Tablets', 'groups' => []];

        $section->importEntry($entry, new ImportPlan());
        $section->importEntry(['comparison' => false] + $entry, new ImportPlan());

        self::assertTrue($repository->saved[0]->offersComparison());
        self::assertFalse($repository->saved[1]->offersComparison());
    }

    public function testComparisonMustBeABoolean(): void
    {
        $errors = $this->phonesSection()->validate([
            ['slug' => 'phones', 'groups' => [], 'comparison' => 'yes'],
        ]);

        self::assertSame(['[0].comparison: must be true or false'], $errors[0]);
    }

    private function phonesSection(): SetsSection
    {
        return new SetsSection(
            $this->phonesRepository(),
            new SpecificationDefinitionRepository(new SpecificationDefinitionMapper())
        );
    }

    private function phonesRepository(): InMemorySetRepository
    {
        return new InMemorySetRepository([
                'phones' => SpecificationSet::fromStorage(
                    1,
                    'Phones',
                    'publish',
                    new SpecificationSetStructure(1, [
                        new SpecificationGroup('general', 'General', 'Basics', true, []),
                    ]),
                    'phones',
                    'Mobile phones'
                ),
            ]);
    }
}
