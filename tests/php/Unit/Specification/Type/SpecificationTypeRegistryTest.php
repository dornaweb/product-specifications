<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Type;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use Amiut\ProductSpecs\Specification\Type\SelectType;
use Amiut\ProductSpecs\Specification\Type\SpecificationType;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use Amiut\ProductSpecs\Specification\Type\TextType;
use PHPUnit\Framework\TestCase;
use WC_Product;

/**
 * Without the fallback an unrecognized type resolves to nothing and the
 * specification disappears from the table. It only fails at runtime.
 */
final class SpecificationTypeRegistryTest extends TestCase
{
    public function testUnknownIdResolvesToTheFallback(): void
    {
        $registry = new SpecificationTypeRegistry(new TextType());

        static::assertFalse($registry->has('rating'));
        static::assertSame(TextType::ID, $registry->get('rating')->id());
    }

    public function testRegisteringOverAnIdReplacesThatType(): void
    {
        $registry = new SpecificationTypeRegistry(new TextType());
        $registry->register(new SelectType());
        $registry->register($this->typeWithId(SelectType::ID, 'Replaced'));

        static::assertSame('Replaced', $registry->get(SelectType::ID)->label());
        static::assertCount(2, $registry->all());
    }

    public function testReplacingTheFallbackTypeAlsoReplacesTheFallback(): void
    {
        $registry = new SpecificationTypeRegistry(new TextType());
        $registry->register($this->typeWithId(TextType::ID, 'Replaced'));

        static::assertSame('Replaced', $registry->get('nothing-registered-here')->label());
    }

    private function typeWithId(string $id, string $label): SpecificationType
    {
        return new class ($id, $label) implements SpecificationType {
            public function __construct(private readonly string $id, private readonly string $label)
            {
            }

            public function id(): string
            {
                return $this->id;
            }

            public function label(): string
            {
                return $this->label;
            }

            /**
             * @return array<string, mixed>
             */
            public function optionsSchema(): array
            {
                return [];
            }

            public function sanitize(mixed $input): mixed
            {
                return $input;
            }

            public function value(WC_Product $product, mixed $stored): mixed
            {
                return $stored;
            }

            public function plainValue(mixed $value, SpecificationOptions $options): string
            {
                return '';
            }

            public function render(mixed $value, SpecificationOptions $options): string
            {
                return '';
            }
        };
    }
}
