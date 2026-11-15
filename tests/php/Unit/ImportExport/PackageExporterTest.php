<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\ImportExport;

use Amiut\ProductSpecs\ImportExport\EntryOutcome;
use Amiut\ProductSpecs\ImportExport\ImportPlan;
use Amiut\ProductSpecs\ImportExport\PackageExporter;
use Amiut\ProductSpecs\ImportExport\Section;
use Amiut\ProductSpecs\ImportExport\SectionRegistry;
use PHPUnit\Framework\TestCase;

final class PackageExporterTest extends TestCase
{
    public function testTheStreamedOutputIsOneValidPackageInDependencyOrder(): void
    {
        $registry = new SectionRegistry();
        $registry->register($this->section('later', ['first'], [['n' => 1], ['n' => 'دو']]));
        $registry->register($this->section('first', [], []));
        $registry->register($this->section('left-out', [], [['n' => 3]]));

        $output = '';
        $counts = (new PackageExporter($registry, '2.0.0'))->export(
            ['first', 'later'],
            static function (string $chunk) use (&$output): void {
                $output .= $chunk;
            }
        );

        $package = json_decode($output, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(['first' => 0, 'later' => 2], $counts);
        self::assertSame('product-specifications', $package['format']);
        self::assertSame(1, $package['schema_version']);
        self::assertSame(['first' => [], 'later' => [['n' => 1], ['n' => 'دو']]], $package['sections']);
    }

    /**
     * @param list<string> $dependsOn
     * @param list<array<string, mixed>> $entries
     */
    private function section(string $key, array $dependsOn, array $entries): Section
    {
        return new class ($key, $dependsOn, $entries) implements Section {
            /**
             * @param list<string> $dependsOn
             * @param list<array<string, mixed>> $entries
             */
            public function __construct(
                private readonly string $key,
                private readonly array $dependsOn,
                private readonly array $entries
            ) {
            }

            public function key(): string
            {
                return $this->key;
            }

            public function dependsOn(): array
            {
                return $this->dependsOn;
            }

            public function validate(array $entries): array
            {
                return [];
            }

            public function export(): iterable
            {
                yield from $this->entries;
            }

            public function planEntry(array $entry, ImportPlan $plan): EntryOutcome
            {
                return EntryOutcome::unchanged($this->key);
            }

            public function importEntry(array $entry, ImportPlan $plan): EntryOutcome
            {
                return EntryOutcome::unchanged($this->key);
            }
        };
    }
}
