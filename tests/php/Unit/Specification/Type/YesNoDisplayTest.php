<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Type;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use Amiut\ProductSpecs\Specification\Type\YesNoDisplay;
use PHPUnit\Framework\TestCase;

final class YesNoDisplayTest extends TestCase
{
    public function testIconsAreTheDefaultAndCarryTheWordForScreenReaders(): void
    {
        $display = YesNoDisplay::fromOptions(SpecificationOptions::fromMap([]));

        self::assertStringContainsString('ps-yes-no--yes', $display->render(true));
        self::assertStringContainsString('aria-label="Yes"', $display->render(true));
        self::assertStringContainsString('ps-yes-no--no', $display->render(false));
    }

    /**
     * @dataProvider textCases
     */
    public function testTextUsesCustomLabelsOnlyWhenBothAreGiven(string $labels, string $yes, string $no): void
    {
        $display = YesNoDisplay::fromOptions(SpecificationOptions::fromMap([
            YesNoDisplay::DISPLAY_KEY => YesNoDisplay::TEXT,
            YesNoDisplay::LABELS_KEY => $labels,
        ]));

        self::assertSame($yes, $display->render(true));
        self::assertSame($no, $display->render(false));
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function textCases(): array
    {
        return [
            'no labels' => ['', 'Yes', 'No'],
            'both labels' => [' Ja | Nein ', 'Ja', 'Nein'],
            'only one label' => ['Ja', 'Yes', 'No'],
            'an empty half' => ['Ja|', 'Yes', 'No'],
            'markup is escaped' => ['<b>Ja</b>|Nein', '&lt;b&gt;Ja&lt;/b&gt;', 'Nein'],
        ];
    }
}
