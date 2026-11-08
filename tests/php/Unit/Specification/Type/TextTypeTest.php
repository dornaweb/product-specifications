<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Type;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use Amiut\ProductSpecs\Specification\Type\TextType;
use PHPUnit\Framework\TestCase;

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
}
