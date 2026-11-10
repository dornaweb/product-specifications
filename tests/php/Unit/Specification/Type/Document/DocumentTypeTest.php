<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Type\Document;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use Amiut\ProductSpecs\Specification\Type\Document\Attachment;
use Amiut\ProductSpecs\Specification\Type\Document\DocumentType;
use PHPUnit\Framework\TestCase;
use WC_Product;

final class DocumentTypeTest extends TestCase
{
    private DocumentType $type;
    private SpecificationOptions $options;

    protected function setUp(): void
    {
        $uploads = 'https://shop.test/uploads/';
        $attachments = (new InMemoryAttachments())
            ->with(new Attachment(482, 'Datasheet X1', $uploads . '2026/11/x1.pdf', '2026/11/x1.pdf', 1258291))
            ->with(new Attachment(517, 'Manual', $uploads . '2026/11/manual.docx', '2026/11/manual.docx', null));

        $this->type = new DocumentType($attachments);
        $this->options = SpecificationOptions::fromMap([]);
    }

    /**
     * @return array<string, array{mixed, list<array{id: int, label?: string}>|null}>
     */
    public static function inputs(): array
    {
        return [
            'files with and without a label' => [
                [['id' => 482, 'label' => ' Datasheet '], ['id' => '517']],
                [['id' => 482, 'label' => 'Datasheet'], ['id' => 517]],
            ],
            'an empty label is left out' => [[['id' => 482, 'label' => '']], [['id' => 482]]],
            'a repeated file is kept once' => [[['id' => 482], ['id' => 482, 'label' => 'Again']], [['id' => 482]]],
            'an invalid id is dropped' => [[['id' => 0], ['id' => -3], ['label' => 'x'], 'junk'], null],
            'a single file that is not in a list' => [['id' => 482], null],
            'a string' => ['datasheet.pdf', null],
        ];
    }

    /**
     * @dataProvider inputs
     * @param list<array{id: int, label?: string}>|null $expected
     */
    public function testSanitize(mixed $input, ?array $expected): void
    {
        static::assertSame($expected, $this->type->sanitize($input, $this->options));
    }

    public function testAFileShowsItsLabelOrElseItsTitleAndAMissingFileIsSkipped(): void
    {
        $value = $this->type->value(
            new WC_Product(),
            [['id' => 482, 'label' => 'Datasheet'], ['id' => 999], ['id' => 517]],
            $this->options
        );

        static::assertSame(['Datasheet', 'Manual'], array_column($value ?? [], 'label'));
        static::assertSame(['PDF', 'DOCX'], array_column($value ?? [], 'type'));
    }

    public function testNoFileLeftIsNoValue(): void
    {
        static::assertNull($this->type->value(new WC_Product(), [['id' => 999]], $this->options));
    }

    public function testEachFileRendersAsAnEscapedLinkWithItsTypeAndSize(): void
    {
        $value = [
            [
                'id' => 1,
                'label' => 'Spec <b>sheet</b>',
                'url' => 'https://shop.test/a.pdf?x="1"',
                'type' => 'PDF',
                'size' => 1258291,
            ],
            ['id' => 2, 'label' => 'Manual', 'url' => 'https://shop.test/m.docx', 'type' => 'DOCX', 'size' => null],
        ];

        static::assertSame(
            '<a href="https://shop.test/a.pdf?x=&quot;1&quot;">Spec &lt;b&gt;sheet&lt;/b&gt;</a> (PDF, 1.2 MB)'
            . '<br><a href="https://shop.test/m.docx">Manual</a> (DOCX)',
            $this->type->render($value, $this->options)
        );
    }

    public function testThePlainValueIsOneLabelAndUrlPerLine(): void
    {
        $value = [
            ['id' => 1, 'label' => 'Datasheet', 'url' => 'https://shop.test/a.pdf', 'type' => 'PDF', 'size' => null],
            ['id' => 2, 'label' => 'Manual', 'url' => 'https://shop.test/m.docx', 'type' => 'DOCX', 'size' => null],
        ];

        static::assertSame(
            "Datasheet: https://shop.test/a.pdf\nManual: https://shop.test/m.docx",
            $this->type->plainValue($value, $this->options)
        );
    }

    public function testAnExportCarriesEachFileAsItsPathInTheUploadsFolder(): void
    {
        static::assertSame(
            [['file' => '2026/11/x1.pdf', 'label' => 'Datasheet'], ['file' => '2026/11/manual.docx']],
            $this->type->exportValue(
                [['id' => 482, 'label' => 'Datasheet'], ['id' => 517], ['id' => 999]],
                $this->options
            )
        );
    }

    public function testAnImportLinksTheFileAtThatPathAndSkipsOneNotFound(): void
    {
        static::assertSame(
            [['id' => 482, 'label' => 'Datasheet']],
            $this->type->importValue(
                [['file' => '2026/11/x1.pdf', 'label' => 'Datasheet'], ['file' => '2026/11/elsewhere.pdf']],
                $this->options
            )
        );
        static::assertNull($this->type->importValue([['file' => '2026/11/elsewhere.pdf']], $this->options));
    }
}
