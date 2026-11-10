<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Type;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use Amiut\ProductSpecs\Specification\Type\TextType;
use PHPUnit\Framework\TestCase;
use WC_Product;

/**
 * The text type is the registry's fallback, so it sanitizes values written by
 * types it knows nothing about. Flattening one into a string would destroy it on
 * the merchant's next save, with nothing to show that it happened.
 */
final class TextTypeTest extends TestCase
{
    private TextType $type;

    protected function setUp(): void
    {
        $this->type = new TextType();
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function nonScalarValues(): array
    {
        return [
            'composite value from another type' => [['6.1', 'in']],
            'map' => [['unit' => 'mm', 'amount' => 12]],
            'null' => [null],
        ];
    }

    /**
     * @dataProvider nonScalarValues
     */
    public function testANonScalarValueIsReturnedUntouched(mixed $value): void
    {
        static::assertSame($value, $this->type->sanitize($value, SpecificationOptions::fromMap([])));
    }

    public function testAScalarIsSanitizedToAString(): void
    {
        static::assertSame('6.1', $this->type->sanitize(6.1, SpecificationOptions::fromMap([])));
        static::assertSame('plain', $this->type->sanitize('plain', SpecificationOptions::fromMap([])));
    }

    public function testAnEmptyStringSanitizesToNull(): void
    {
        static::assertNull($this->type->sanitize('', SpecificationOptions::fromMap([])));
    }

    /**
     * @return array<string, array{mixed, list<string>|null}>
     */
    public static function severalValueInputs(): array
    {
        return [
            'a string becomes a one-item list' => ['HDMI', ['HDMI']],
            'items are trimmed' => [[' USB-C ', 'HDMI'], ['USB-C', 'HDMI']],
            'a variant of an earlier item is dropped' => [['Blue', 'blue ', 'BLUE'], ['Blue']],
            'empty and non-string items are dropped' => [['', 'HDMI', ['nested'], null], ['HDMI']],
            'a comma stays inside its item' => [['Black, matte'], ['Black, matte']],
            'an empty list is no value' => [[], null],
            'a list of empties is no value' => [['', ' '], null],
        ];
    }

    /**
     * @dataProvider severalValueInputs
     * @param list<string>|null $expected
     */
    public function testSeveralValuesAreStoredAsAList(mixed $input, ?array $expected): void
    {
        static::assertSame($expected, $this->type->sanitize($input, self::several()));
    }

    public function testAListIsLeftAloneWhenSeveralValuesAreOff(): void
    {
        static::assertSame(['USB-C', 'HDMI'], $this->type->sanitize(['USB-C', 'HDMI'], self::single()));
    }

    public function testAStoredStringReadsAsAOneItemListWhenSeveralValuesAreOn(): void
    {
        static::assertSame(['HDMI'], $this->type->value(new WC_Product(), 'HDMI', self::several()));
        static::assertSame('HDMI', $this->type->value(new WC_Product(), 'HDMI', self::single()));
    }

    public function testAStoredListReadsAsAListWhenSeveralValuesAreOff(): void
    {
        static::assertSame(['USB-C', 'HDMI'], $this->type->value(new WC_Product(), ['USB-C', 'HDMI'], self::single()));
        static::assertNull($this->type->value(new WC_Product(), [], self::single()));
    }

    public function testAMapIsNotReadAsAList(): void
    {
        static::assertNull($this->type->value(new WC_Product(), ['unit' => 'mm'], self::several()));
    }

    public function testAListRendersEachItemEscapedAndJoined(): void
    {
        static::assertSame(
            'USB-C, &lt;b&gt;HDMI&lt;/b&gt;',
            $this->type->render(['USB-C', '<b>HDMI</b>'], self::single())
        );
        static::assertSame('USB-C, <b>HDMI</b>', $this->type->plainValue(['USB-C', '<b>HDMI</b>'], self::single()));
    }

    private static function several(): SpecificationOptions
    {
        return SpecificationOptions::fromMap(['multiple' => true]);
    }

    private static function single(): SpecificationOptions
    {
        return SpecificationOptions::fromMap([]);
    }
}
