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
                            SpecificationSetItem::create(10, '%d9%88%d8%b2%d9%86', false, true),
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

    private function phonesSection(): SetsSection
    {
        return new SetsSection(
            new InMemorySetRepository([
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
            ]),
            new SpecificationDefinitionRepository(new SpecificationDefinitionMapper())
        );
    }
}
