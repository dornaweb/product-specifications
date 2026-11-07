<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Migration\Step;

use Amiut\ProductSpecs\Migration\Step\SnapshotValueReader;
use Amiut\ProductSpecs\Migration\Step\SpecificationLookup;
use Amiut\ProductSpecs\Specification\SpecificationDefinition;
use Amiut\ProductSpecs\Specification\SpecificationOptions;
use Amiut\ProductSpecs\Specification\Type\SelectType;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use Amiut\ProductSpecs\Specification\Type\TextType;
use Amiut\ProductSpecs\Specification\Type\TrueFalseType;
use PHPUnit\Framework\TestCase;

final class SnapshotValueReaderTest extends TestCase
{
    public function testValuesAreKeyedByTheCurrentSlugAndConvertedByTheirType(): void
    {
        $read = $this->reader()->read([
            [
                'group_id' => 1,
                'attributes' => [
                    $this->entry(10, 'screen', '15&#34; panel'),
                    $this->entry(11, 'wifi', 'yes'),
                    $this->entry(12, 'bluetooth', 'no'),
                    $this->entry(13, 'notes', ''),
                    $this->entry(14, 'color', 'dwspecs_chk_none'),
                ],
            ],
        ]);

        self::assertSame(
            ['screen-size-a1b2c3d4' => '15" panel', 'wifi' => true, 'bluetooth' => false],
            $read['values']
        );
        self::assertSame([], $read['unresolved']);
    }

    public function testADeletedSpecificationIsRecreatedAndAnUnrecoverableOneIsReported(): void
    {
        $read = $this->reader()->read([
            ['attributes' => [$this->entry(98, 'warranty', '2 years'), $this->entry(99, 'gone', 'x', 'Gone')]],
        ]);

        self::assertSame(['warranty-recreated' => '2 years'], $read['values']);
        self::assertSame(['Gone'], $read['unresolved']);
    }

    public function testASnapshotThatIsNotAListOfGroupsReadsAsNothing(): void
    {
        foreach (['', 'a:broken', [['attributes' => 'nope']], [['attributes' => ['nope']]]] as $snapshot) {
            self::assertSame(['values' => [], 'unresolved' => []], $this->reader()->read($snapshot));
        }
    }

    /**
     * @return array{attr_id: int, attr_name: string, attr_slug: string, attr_desc: string, value: string}
     */
    private function entry(int $id, string $slug, string $value, string $name = 'Name'): array
    {
        return ['attr_id' => $id, 'attr_name' => $name, 'attr_slug' => $slug, 'attr_desc' => '', 'value' => $value];
    }

    private function reader(): SnapshotValueReader
    {
        $types = new SpecificationTypeRegistry(new TextType());
        $types->register(new SelectType());
        $types->register(new TrueFalseType());

        $lookup = new class implements SpecificationLookup {
            public function find(int $id): ?SpecificationDefinition
            {
                $known = [
                    10 => ['screen-size-a1b2c3d4', TextType::ID],
                    11 => ['wifi', TrueFalseType::ID],
                    12 => ['bluetooth', TrueFalseType::ID],
                    13 => ['notes', TextType::ID],
                    14 => ['color', SelectType::ID],
                ];

                return isset($known[$id]) ? self::definition($id, ...$known[$id]) : null;
            }

            public function recreate(
                int $formerId,
                string $name,
                string $slug,
                string $description
            ): ?SpecificationDefinition {
                return $name === 'Gone' ? null : self::definition(500, $slug . '-recreated', TextType::ID);
            }

            private static function definition(int $id, string $key, string $typeId): SpecificationDefinition
            {
                return SpecificationDefinition::fromStorage(
                    $id,
                    $key,
                    $key,
                    '',
                    $typeId,
                    SpecificationOptions::fromMap([]),
                    null
                );
            }
        };

        return new SnapshotValueReader($lookup, $types);
    }
}
