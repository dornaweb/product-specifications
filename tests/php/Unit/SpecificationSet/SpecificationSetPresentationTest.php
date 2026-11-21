<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\SpecificationSet;

use Amiut\ProductSpecs\SpecificationSet\SpecificationSet;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetPresentation;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetStructure;
use PHPUnit\Framework\TestCase;

final class SpecificationSetPresentationTest extends TestCase
{
    /**
     * @dataProvider documentsWithoutComparison
     */
    public function testADocumentThatDoesNotTurnComparisonOnReadsAsOff(mixed $stored): void
    {
        self::assertFalse(SpecificationSetPresentation::fromStored($stored)->offersComparison());
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function documentsWithoutComparison(): array
    {
        return [
            'no stored document' => [''],
            'not an object' => ['comparison'],
            'no comparison member' => [['schema_version' => 1, 'empty_rows' => 'show']],
            'a truthy non-boolean' => [['comparison' => 'yes']],
        ];
    }

    public function testKnownMembersAreKeptAndUnknownOrInvalidOnesAreReplaced(): void
    {
        $stored = [
            'schema_version' => 7,
            'empty_rows' => 'show',
            'empty_groups' => 'sometimes',
            'comparison' => true,
            'layout' => 'cards',
        ];

        self::assertSame(
            ['schema_version' => 1, 'empty_rows' => 'show', 'empty_groups' => 'hide', 'comparison' => true],
            SpecificationSetPresentation::fromStored($stored)->toArray()
        );
    }

    public function testANewSetOffersComparisonAndAStoredSetWithoutADocumentDoesNot(): void
    {
        $structure = SpecificationSetStructure::empty();

        self::assertTrue(SpecificationSet::create('Phones', 'publish', $structure)->offersComparison());
        self::assertFalse(SpecificationSet::fromStorage(3, 'Phones', 'publish', $structure)->offersComparison());
    }
}
