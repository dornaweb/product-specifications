<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification;

use Amiut\ProductSpecs\Specification\SpecificationSlug;
use PHPUnit\Framework\TestCase;

final class SpecificationSlugTest extends TestCase
{
    public function testATypedKeyIsSanitized(): void
    {
        self::assertSame('screen-size', SpecificationSlug::fromInput(' Screen Size ')?->value());
        self::assertSame('2024-model', SpecificationSlug::fromInput('2024 model')?->value());
        self::assertSame('02024', SpecificationSlug::fromInput('02024')?->value());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function unusableKeys(): array
    {
        return [
            'nothing left after sanitizing' => ['!!!'],
            'empty' => [''],
            'a whole number' => ['2024'],
        ];
    }

    /**
     * @dataProvider unusableKeys
     */
    public function testAKeyThatCannotBeStoredIsRefused(string $input): void
    {
        self::assertNull(SpecificationSlug::fromInput($input));
    }

    public function testANameDerivesAKeyEvenWhenNothingUsableRemains(): void
    {
        self::assertSame('weight', SpecificationSlug::fromName('Weight')->value());
        self::assertSame('specification', SpecificationSlug::fromName('???')->value());
        self::assertTrue(SpecificationSlug::fromName('2024')->isCanonicalInteger());
    }

    public function testALongKeyFitsTheSlugColumnWithoutATrailingDash(): void
    {
        $slug = SpecificationSlug::fromInput(str_repeat('screen size ', 40))?->value();

        self::assertNotNull($slug);
        self::assertLessThanOrEqual(200, strlen($slug));
        self::assertStringEndsNotWith('-', $slug);
    }

    public function testANumericSuffixKeepsTheWholeKeyInsideTheColumn(): void
    {
        self::assertSame('weight-2', SpecificationSlug::preserved('weight')->withNumericSuffix(2)->value());

        $long = SpecificationSlug::preserved(str_repeat('a', 199))->withNumericSuffix(12)->value();

        self::assertSame(200, strlen($long));
        self::assertStringEndsWith('-12', $long);
    }

    public function testACutNeverLandsInsideAPercentEncodedCharacter(): void
    {
        $slug = SpecificationSlug::preserved(str_repeat('%e4%b8%ad', 23))->withNumericSuffix(12)->value();
        $stem = substr($slug, 0, -3);

        self::assertLessThanOrEqual(200, strlen($slug));
        self::assertStringEndsWith('-12', $slug);
        self::assertSame(0, strlen($stem) % 9);
        self::assertTrue(mb_check_encoding(rawurldecode($stem), 'UTF-8'));
    }
}
