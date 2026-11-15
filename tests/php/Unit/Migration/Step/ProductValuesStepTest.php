<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Migration\Step;

use Amiut\ProductSpecs\Migration\Step\ProductValuesStep;
use Amiut\ProductSpecs\Migration\Step\SnapshotValueReader;
use Amiut\ProductSpecs\Migration\Step\SpecificationLookup;
use Amiut\ProductSpecs\ProductValues\WordPressProductValueRepository;
use Amiut\ProductSpecs\Specification\SpecificationDefinition;
use Amiut\ProductSpecs\Specification\SpecificationOptions;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use Amiut\ProductSpecs\Specification\Type\TextType;
use PHPUnit\Framework\TestCase;

final class ProductValuesStepTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['wp_test_posts'] = [10, 11];
        $GLOBALS['wp_test_post_meta'] = [
            10 => ['_dwps_specification_table' => $this->snapshot('2 years')],
            11 => ['_dwps_specification_table' => $this->snapshot('3 years')],
        ];
    }

    public function testAChunkRunAgainDoesNotReportProductsItAlreadyMigratedAsSkipped(): void
    {
        $step = $this->step();
        $step->run(0, 1);

        $rerun = $step->run(0, 2);

        self::assertSame([], $rerun->skipped());
        self::assertSame(1, $rerun->processed());
        self::assertSame(
            ['schema_version' => 1, 'values' => ['warranty' => '3 years']],
            $GLOBALS['wp_test_post_meta'][11]['product_specifications_values']
        );
    }

    public function testAProductHoldingDataBeforeTheRunIsSkipped(): void
    {
        $GLOBALS['wp_test_post_meta'][10]['product_specifications_set'] = 'phones';

        $result = $this->step()->run(0, 2);

        self::assertSame([10], $result->skipped());
        self::assertSame(1, $result->processed());
    }

    /**
     * @return list<array{attributes: list<array<string, int|string>>}>
     */
    private function snapshot(string $value): array
    {
        return [[
            'attributes' => [
                [
                    'attr_id' => 1,
                    'attr_name' => 'Warranty',
                    'attr_slug' => 'warranty',
                    'attr_desc' => '',
                    'value' => $value,
                ],
            ],
        ]];
    }

    private function step(): ProductValuesStep
    {
        $lookup = new class implements SpecificationLookup {
            public function find(int $id): ?SpecificationDefinition
            {
                return SpecificationDefinition::fromStorage(
                    $id,
                    'warranty',
                    'warranty',
                    '',
                    TextType::ID,
                    SpecificationOptions::fromMap([]),
                    null
                );
            }

            public function recreate(
                int $formerId,
                string $name,
                string $slug,
                string $description
            ): ?SpecificationDefinition {
                return null;
            }
        };

        return new ProductValuesStep(
            new SnapshotValueReader($lookup, new SpecificationTypeRegistry(new TextType())),
            new WordPressProductValueRepository()
        );
    }
}
