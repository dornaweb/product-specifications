<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Suggestion;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use Amiut\ProductSpecs\Specification\Type\TextOptions;
use Amiut\ProductSpecs\Suggestion\ValueUsage;
use PHPUnit\Framework\TestCase;

/**
 * Wrong counts or a wrong order do not fail anything; they only offer the editor the
 * wrong values, so the rules are pinned here.
 */
final class ValueUsageTest extends TestCase
{
    public function testAProductIsCountedOncePerValue(): void
    {
        $usage = ValueUsage::empty()->withChange(null, ['Blue', 'blue ', 'Navy']);

        static::assertSame(
            ['blue' => ['Blue' => 1], 'navy' => ['Navy' => 1]],
            $usage->toStorage()['values']
        );
    }

    public function testAChangedValueMovesItsCount(): void
    {
        $usage = ValueUsage::empty()
            ->withChange(null, 'Blue')
            ->withChange(null, 'Blue')
            ->withChange('Blue', 'Navy');

        static::assertSame(
            ['blue' => ['Blue' => 1], 'navy' => ['Navy' => 1]],
            $usage->toStorage()['values']
        );
    }

    public function testAValueNoProductCarriesIsDropped(): void
    {
        $usage = ValueUsage::empty()
            ->withChange(null, 'Navvy')
            ->withChange('Navvy', null);

        static::assertSame([], $usage->toStorage()['values']);
    }

    public function testRemovingAValueTheIndexNeverCountedChangesNothing(): void
    {
        $usage = ValueUsage::empty()->withChange(null, 'Blue')->withChange('Navy', null);

        static::assertSame(['blue' => ['Blue' => 1]], $usage->toStorage()['values']);
    }

    public function testASuggestionIsRankedByHowManyProductsCarryIt(): void
    {
        $usage = self::usage(['Navy' => 3, 'Graphite' => 5, 'Blue' => 1]);

        static::assertSame(
            ['Graphite', 'Navy', 'Blue'],
            self::values($usage->suggestions(self::options([], learn: true)))
        );
    }

    public function testOnATieATypedSuggestionComesFirstAndKeepsItsOrder(): void
    {
        $usage = self::usage(['Navy' => 1, 'Black' => 1, 'White' => 1]);

        static::assertSame(
            ['White', 'Black', 'Navy'],
            self::values($usage->suggestions(self::options(['White', 'Black'], learn: true)))
        );
    }

    public function testATypedSuggestionCountsEverySpellingOfIt(): void
    {
        $usage = self::usage(['blue' => 2, 'BLUE' => 1, 'Navy' => 2]);
        $suggestions = $usage->suggestions(self::options(['Blue'], learn: true));

        static::assertSame(['value' => 'Blue', 'count' => 3, 'learned' => false], $suggestions[0]);
    }

    public function testALearnedValueShowsTheSpellingMostProductsCarry(): void
    {
        $usage = self::usage(['navy' => 1, 'Navy' => 2]);

        static::assertSame(
            [['value' => 'Navy', 'count' => 3, 'learned' => true]],
            $usage->suggestions(self::options([], learn: true))
        );
    }

    public function testSpellingsCarriedEquallyOftenResolveTheSameWayEveryTime(): void
    {
        $usage = self::usage(['navy' => 1, 'Navy' => 1]);

        static::assertSame(['Navy'], self::values($usage->suggestions(self::options([], learn: true))));
    }

    public function testLearnedValuesAreLeftOutWhileLearningIsOff(): void
    {
        $usage = self::usage(['Navy' => 4]);

        static::assertSame(['Blue'], self::values($usage->suggestions(self::options(['Blue'], learn: false))));
    }

    public function testAVariantOfATypedSuggestionIsNotAlsoLearned(): void
    {
        $usage = self::usage(['blue' => 4]);

        static::assertSame(['Blue'], self::values($usage->suggestions(self::options(['Blue', 'BLUE'], learn: true))));
    }

    public function testLearnedValuesAreCappedKeepingTheMostCarried(): void
    {
        $counts = ['Rare' => 1];

        for ($index = 0; $index < ValueUsage::LEARNED_LIMIT; ++$index) {
            $counts["Value {$index}"] = 2;
        }

        $values = self::values(self::usage($counts)->suggestions(self::options(['Typed'], learn: true)));

        static::assertCount(ValueUsage::LEARNED_LIMIT + 1, $values);
        static::assertContains('Typed', $values);
        static::assertNotContains('Rare', $values);
    }

    public function testAMalformedDocumentReadsAsEmpty(): void
    {
        static::assertSame([], ValueUsage::fromStorage('broken')->toStorage()['values']);
        static::assertSame(
            ['blue' => ['Blue' => 2]],
            ValueUsage::fromStorage(['values' => ['blue' => ['Blue' => 2, 'blue' => 0], 'navy' => 'x']])
                ->toStorage()['values']
        );
    }

    /**
     * @param array<string, int> $counts
     */
    private static function usage(array $counts): ValueUsage
    {
        $usage = ValueUsage::empty();

        foreach ($counts as $value => $count) {
            for ($product = 0; $product < $count; ++$product) {
                $usage = $usage->withChange(null, (string) $value);
            }
        }

        return $usage;
    }

    /**
     * @param list<string> $typed
     */
    private static function options(array $typed, bool $learn): TextOptions
    {
        return TextOptions::of(SpecificationOptions::fromMap([
            'suggest' => true,
            'values' => $typed,
            'learn' => $learn,
        ]));
    }

    /**
     * @param list<array{value: string, count: int, learned: bool}> $suggestions
     * @return list<string>
     */
    private static function values(array $suggestions): array
    {
        return array_column($suggestions, 'value');
    }
}
