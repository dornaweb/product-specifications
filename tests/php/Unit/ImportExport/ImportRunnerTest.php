<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\ImportExport;

use Amiut\ProductSpecs\ImportExport\EntryOutcome;
use Amiut\ProductSpecs\ImportExport\Import\ImportRun;
use Amiut\ProductSpecs\ImportExport\Import\ImportRunner;
use Amiut\ProductSpecs\ImportExport\Import\ImportRunRepository;
use Amiut\ProductSpecs\ImportExport\Import\ImportStorage;
use Amiut\ProductSpecs\ImportExport\ImportPlan;
use Amiut\ProductSpecs\ImportExport\ImportPlanner;
use Amiut\ProductSpecs\ImportExport\PackageFormat;
use Amiut\ProductSpecs\ImportExport\PackageImporter;
use Amiut\ProductSpecs\ImportExport\PackageReader;
use Amiut\ProductSpecs\ImportExport\Section;
use Amiut\ProductSpecs\ImportExport\SectionRegistry;
use Amiut\ProductSpecs\Upgrade\DataVersion;
use Amiut\ProductSpecs\Upgrade\DataVersionRepository;
use Amiut\ProductSpecs\Upgrade\MigrationState;
use PHPUnit\Framework\TestCase;

final class ImportRunnerTest extends TestCase
{
    private string $uploads;

    protected function setUp(): void
    {
        $this->uploads = sys_get_temp_dir() . '/ps-import-' . bin2hex(random_bytes(4));
        $GLOBALS['wp_test_upload_dir'] = $this->uploads;
        $GLOBALS['wp_test_options'] = [DataVersionRepository::OPTION => DataVersion::CURRENT_VERSION];
    }

    // phpcs:disable WordPressVIPMinimum.Functions.RestrictedFunctions.directory_rmdir
    protected function tearDown(): void
    {
        foreach (glob($this->uploads . '/*/{,.}[!.]*', GLOB_BRACE) ?: [] as $file) {
            unlink($file);
        }

        foreach (glob($this->uploads . '/*') ?: [] as $directory) {
            rmdir($directory);
        }

        if (is_dir($this->uploads)) {
            rmdir($this->uploads);
        }

        unset($GLOBALS['wp_test_upload_dir']);
    }
    // phpcs:enable WordPressVIPMinimum.Functions.RestrictedFunctions.directory_rmdir

    public function testChunksContinueWhereTheLastOneStoppedAndTheFileIsRemovedAtTheEnd(): void
    {
        $section = new RecordingSection();
        $runner = $this->runner($section);
        $entries = ImportRunner::CHUNK_SIZE + 5;

        $planned = $runner->prepare($this->package($entries), 'catalog.json');
        $storedFile = $planned->storedFile();
        $runner->start();
        $runner->runChunk();

        self::assertCount(ImportRunner::CHUNK_SIZE, $section->imported);
        self::assertTrue($runner->isRunning());

        $runner->runChunk();
        $run = $runner->run();

        self::assertSame(range(0, $entries - 1), $section->imported);
        self::assertSame(ImportRun::COMPLETE, $run?->status());
        self::assertSame($entries, $run->processed());
        self::assertFileDoesNotExist($storedFile);
        self::assertFileDoesNotExist($planned->planFile());
        self::assertCount($entries, $runner->report()[RecordingSection::KEY] ?? []);
    }

    public function testAResumedRunStartsAtTheEntryThatFailedWithoutRepeatingTheOnesBeforeIt(): void
    {
        $section = new RecordingSection(failOn: 3);
        $runner = $this->runner($section);

        $runner->prepare($this->package(6), 'catalog.json');
        $runner->start();
        $runner->runChunk();

        self::assertSame(ImportRun::FAILED, $runner->run()?->status());
        self::assertSame('Entry 3 broke.', $runner->run()->failure());

        $section->failOn = null;
        $runner->start();
        $runner->runChunk();

        self::assertSame([0, 1, 2, 3, 4, 5], $section->imported);
        self::assertSame(6, $runner->run()?->processed());
        self::assertSame(ImportRun::COMPLETE, $runner->run()->status());
        self::assertSame([0, 1, 2, 3, 4, 5], array_keys($runner->report()[RecordingSection::KEY] ?? []));
    }

    private function runner(Section $section): ImportRunner
    {
        $registry = new SectionRegistry();
        $registry->register($section);

        return new ImportRunner(
            new ImportRunRepository(),
            new ImportStorage(),
            new PackageReader($registry),
            new ImportPlanner($registry),
            new PackageImporter($registry),
            new MigrationState(new DataVersionRepository())
        );
    }

    private function package(int $entries): string
    {
        return (string) json_encode([
            'format' => PackageFormat::NAME,
            'schema_version' => PackageFormat::SCHEMA_VERSION,
            'sections' => [
                RecordingSection::KEY => array_map(
                    static fn (int $index): array => ['index' => $index],
                    range(0, $entries - 1)
                ),
            ],
        ]);
    }
}
