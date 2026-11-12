<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\SpecificationSet;

use Amiut\ProductSpecs\SpecificationSet\StructureMetaDefinition;
use PHPUnit\Framework\TestCase;

/**
 * The `required` lists are a contract. WordPress does not apply a nested
 * `default`, so anything listed here is genuinely mandatory on the wire and a
 * later edit that re-adds one silently rejects partial payloads again.
 */
final class StructureMetaDefinitionTest extends TestCase
{
    public function testAGroupRequiresOnlyWhatCannotBeDefaulted(): void
    {
        static::assertSame(
            ['key', 'label', 'items'],
            $this->groupSchema()['required']
        );
    }

    public function testAnItemRequiresOnlyWhatCannotBeDefaulted(): void
    {
        static::assertSame(
            ['term_id', 'slug'],
            $this->itemSchema()['required']
        );
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function defaultedPropertyProvider(): array
    {
        return [
            'group visible' => ['group', 'visible'],
            'item visible' => ['item', 'visible'],
        ];
    }

    /**
     * @dataProvider defaultedPropertyProvider
     */
    public function testADefaultedPropertyIsStillDeclared(string $level, string $property): void
    {
        $schema = $level === 'group' ? $this->groupSchema() : $this->itemSchema();

        static::assertArrayHasKey($property, $schema['properties']);
        static::assertArrayHasKey('default', $schema['properties'][$property]);
    }

    /**
     * @return array<string, mixed>
     */
    private function groupSchema(): array
    {
        return (new StructureMetaDefinition())->schema()['properties']['groups']['items'];
    }

    /**
     * @return array<string, mixed>
     */
    private function itemSchema(): array
    {
        return $this->groupSchema()['properties']['items']['items'];
    }
}
