<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Migration\Step;

use Amiut\ProductSpecs\Migration\Step\SpecificationDefinitionNormalizer;
use Amiut\ProductSpecs\Specification\Deletion\PurgeQueue;
use Amiut\ProductSpecs\Specification\SlugAvailability;
use PHPUnit\Framework\TestCase;
use WP_Term;

final class SpecificationDefinitionNormalizerTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['wp_test_options'] = [];
        $GLOBALS['wp_test_terms'] = [];
    }

    public function testRadioBecomesSelectAndEveryOtherTypeIsKept(): void
    {
        $normalizer = $this->normalizer();

        self::assertSame('select', $normalizer->typeId('radio'));

        foreach (['text', 'textarea', 'select', 'true_false', '', 'rating'] as $typeId) {
            self::assertSame($typeId, $normalizer->typeId($typeId));
        }
    }

    /**
     * @return array<string, array{mixed, mixed}>
     */
    public static function selectDefaultProvider(): array
    {
        return [
            'one of the choices' => ['6.1"', '6.1"'],
            'the literal string Array' => ['Array', null],
            'a value not among the choices' => ['7"', null],
            'no default' => ['', null],
            'a list' => [['6.1"'], null],
        ];
    }

    /**
     * @dataProvider selectDefaultProvider
     */
    public function testASelectDefaultSurvivesOnlyAsOneOfItsChoices(mixed $stored, mixed $expected): void
    {
        self::assertSame(
            $expected,
            $this->normalizer()->defaultValue('select', $stored, ['5.5"', '6.1"'])
        );
    }

    public function testDefaultsOfOtherTypesAreKeptDecoded(): void
    {
        $normalizer = $this->normalizer();

        self::assertSame('1', $normalizer->defaultValue('true_false', '1', []));
        self::assertSame('Array', $normalizer->defaultValue('text', 'Array', []));
        self::assertSame('R&D "lab"', $normalizer->defaultValue('text', 'R&#38;D &#34;lab&#34;', []));
    }

    public function testEncodedChoicesAreDecoded(): void
    {
        self::assertSame(
            ['6.1"', 'R&D', "O'Neill", '< 5 kg'],
            $this->normalizer()->choices(['6.1&quot;', 'R&amp;D', 'O&#039;Neill', '&lt; 5 kg'])
        );
    }

    public function testAChoiceListBecomesTheOptionsMap(): void
    {
        self::assertSame(
            ['values' => ['6.1"', 'R&D']],
            $this->normalizer()->options([0 => '6.1&quot;', 3 => 'R&amp;D'])
        );
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function storedOptionsKeptAsTheyAre(): array
    {
        return [
            'an options map' => [['values' => ['6.1"']]],
            'no options' => [''],
            'an empty list' => [[]],
        ];
    }

    /**
     * @dataProvider storedOptionsKeptAsTheyAre
     */
    public function testStoredOptionsThatAreNotAChoiceListAreKept(mixed $stored): void
    {
        self::assertSame($stored, $this->normalizer()->options($stored));
    }

    public function testADefaultEncodedDifferentlyFromItsChoiceStillMatchesIt(): void
    {
        $normalizer = $this->normalizer();
        $choices = $normalizer->choices(['5.5&quot;', '6.1&quot;']);

        self::assertSame('6.1"', $normalizer->defaultValue('select', '6.1&#34;', $choices));
    }

    public function testOnlyACanonicalIntegerSlugGainsASuffix(): void
    {
        $normalizer = $this->normalizer();

        self::assertSame('2024-2', $normalizer->slug('2024'));
        self::assertSame('screen-size', $normalizer->slug('screen-size'));
        self::assertSame('2024-model', $normalizer->slug('2024-model'));
        self::assertSame('02024', $normalizer->slug('02024'));
    }

    public function testTheSuffixSkipsAKeyAnotherSpecificationHolds(): void
    {
        $term = new WP_Term();
        $term->term_id = 3;
        $term->slug = '2024-2';
        $GLOBALS['wp_test_terms'] = [3 => $term];

        self::assertSame('2024-3', $this->normalizer()->slug('2024'));
    }

    private function normalizer(): SpecificationDefinitionNormalizer
    {
        return new SpecificationDefinitionNormalizer(new SlugAvailability(new PurgeQueue()));
    }
}
