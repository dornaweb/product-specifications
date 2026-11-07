<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Migration\Step;

use Amiut\ProductSpecs\Migration\Step\SetStructureBuilder;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetItem;
use PHPUnit\Framework\TestCase;

final class SetStructureBuilderTest extends TestCase
{
    public function testArrangedMembersComeFirstAndTheRestFollowByName(): void
    {
        $group = (new SetStructureBuilder())->group(
            7,
            'Display',
            'Screen and panel',
            [30, 99, 10],
            [
                ['id' => 10, 'slug' => 'size', 'name' => 'Size'],
                ['id' => 20, 'slug' => 'refresh', 'name' => 'Refresh rate'],
                ['id' => 30, 'slug' => 'panel', 'name' => 'Panel'],
                ['id' => 40, 'slug' => 'brightness', 'name' => 'Brightness'],
            ]
        );

        self::assertSame(
            [30, 10, 40, 20],
            array_map(static fn (SpecificationSetItem $item): int => $item->termId(), $group->items())
        );
    }

    public function testTheGroupCarriesTheTermsNameDescriptionAndAStableKey(): void
    {
        $group = (new SetStructureBuilder())->group(
            7,
            'Display',
            'Screen and panel',
            [],
            [['id' => 10, 'slug' => '%d8%b1%d9%86%da%af', 'name' => 'رنگ']]
        );

        self::assertSame('group-7', $group->key());
        self::assertSame('Display', $group->label());
        self::assertSame('Screen and panel', $group->description());
        self::assertTrue($group->visible());
        self::assertSame('%d8%b1%d9%86%da%af', $group->items()[0]->slug());
        self::assertTrue($group->items()[0]->visible());
        self::assertFalse($group->items()[0]->comparable());
    }
}
