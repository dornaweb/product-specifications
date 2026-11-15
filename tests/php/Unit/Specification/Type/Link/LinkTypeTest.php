<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Type\Link;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use Amiut\ProductSpecs\Specification\Type\Link\LinkType;
use PHPUnit\Framework\TestCase;

final class LinkTypeTest extends TestCase
{
    private const REJECTION = 'Enter a URL that starts with https://, http://, mailto: or tel:.';

    /**
     * @return array<string, array{mixed, array<string, string>|null}>
     */
    public static function entered(): array
    {
        $drivers = 'https://example.com/drivers';

        return [
            'a web address with a label' => [
                ['url' => $drivers, 'label' => 'Drivers'],
                ['url' => $drivers, 'label' => 'Drivers'],
            ],
            'an empty label' => [['url' => $drivers, 'label' => ' '], ['url' => $drivers]],
            'a label with markup' => [
                ['url' => $drivers, 'label' => '<b>Drivers</b>'],
                ['url' => $drivers, 'label' => 'Drivers'],
            ],
            'http' => [['url' => 'http://example.com'], ['url' => 'http://example.com']],
            'a bare address' => [$drivers, ['url' => $drivers]],
            'an email address' => [
                ['url' => 'mailto:info@example.com'],
                ['url' => 'mailto:info@example.com'],
            ],
            'a phone number' => [['url' => 'tel:+49-30-1234567'], ['url' => 'tel:+49-30-1234567']],
            'a web address without a scheme' => [['url' => 'www.example.com'], null],
            'an email address without a scheme' => [['url' => 'info@example.com'], null],
            'plain text' => [['url' => 'Drivers page'], null],
            'a bare word' => ['Red', null],
            'a host with whitespace' => [['url' => 'https://Drivers page'], null],
            'mailto' => [['url' => 'MAILTO:info@example.com'], ['url' => 'MAILTO:info@example.com']],
            'a script' => [['url' => 'javascript:alert(1)'], null],
            'another scheme' => [['url' => 'ftp://example.com'], null],
            'a web address without a host' => [['url' => 'https://'], null],
            'mailto without an address' => [['url' => 'mailto:'], null],
            'a label without an address' => [['label' => 'Drivers'], null],
            'an address that is not text' => [['url' => 42], null],
            'nothing entered' => [['url' => '', 'label' => ''], null],
        ];
    }

    /**
     * @dataProvider entered
     * @param array<string, string>|null $stored
     */
    public function testAnEnteredLinkIsStoredAsAnAddressTheTypeAccepts(mixed $input, ?array $stored): void
    {
        self::assertSame($stored, (new LinkType())->sanitize($input, $this->noOptions()));
    }

    /**
     * @return array<string, array{mixed, string|null}>
     */
    public static function validated(): array
    {
        return [
            'nothing entered' => [['url' => '', 'label' => ''], null],
            'no value' => [null, null],
            'a web address' => [['url' => 'https://www.example.com', 'label' => 'Drivers'], null],
            'a web address without a scheme' => [['url' => 'www.example.com'], self::REJECTION],
            'a script' => [['url' => 'javascript:alert(1)'], self::REJECTION],
            'a label without an address' => [['url' => '', 'label' => 'Drivers'], self::REJECTION],
            'a web address without a host' => [['url' => 'https://'], self::REJECTION],
            'a number' => [42, self::REJECTION],
        ];
    }

    /**
     * @dataProvider validated
     */
    public function testInputThatIsNotEmptyAndCannotBeStoredIsRejected(mixed $input, ?string $rejection): void
    {
        self::assertSame($rejection, (new LinkType())->validate($input, $this->noOptions()));
    }

    /**
     * @return array<string, array{mixed, string}>
     */
    public static function rendered(): array
    {
        return [
            'the label' => [
                ['url' => 'https://example.com/drivers', 'label' => 'Drivers'],
                '<a href="https://example.com/drivers" target="_blank" rel="nofollow noopener">Drivers</a>',
            ],
            'the host without a label' => [
                ['url' => 'https://www.example.com/drivers?model=4'],
                '<a href="https://www.example.com/drivers?model=4" target="_blank" rel="nofollow noopener">'
                . 'www.example.com</a>',
            ],
            'the email address without a label' => [
                ['url' => 'mailto:support@example.com?subject=Drivers'],
                '<a href="mailto:support@example.com?subject=Drivers" target="_blank" rel="nofollow noopener">'
                . 'support@example.com</a>',
            ],
            'the number without a label' => [
                ['url' => 'tel:+49%2030%201234567'],
                '<a href="tel:+49%2030%201234567" target="_blank" rel="nofollow noopener">+49 30 1234567</a>',
            ],
            'an escaped label' => [
                ['url' => 'https://example.com', 'label' => 'Drivers & manuals'],
                '<a href="https://example.com" target="_blank" rel="nofollow noopener">Drivers &amp; manuals</a>',
            ],
            'a stored text address' => [
                'https://example.com/drivers',
                '<a href="https://example.com/drivers" target="_blank" rel="nofollow noopener">example.com</a>',
            ],
            'a stored script' => [['url' => 'javascript:alert(1)', 'label' => 'Drivers'], ''],
        ];
    }

    /**
     * @dataProvider rendered
     */
    public function testALinkRendersAsItsLabelOrItsShortAddress(mixed $value, string $html): void
    {
        self::assertSame($html, (new LinkType())->render($value, $this->noOptions()));
    }

    public function testThePlainValuePutsTheLabelBeforeTheAddress(): void
    {
        $type = new LinkType();

        self::assertSame(
            'Drivers: https://example.com/drivers',
            $type->plainValue(['url' => 'https://example.com/drivers', 'label' => 'Drivers'], $this->noOptions())
        );
        self::assertSame(
            'https://example.com/drivers',
            $type->plainValue(['url' => 'https://example.com/drivers'], $this->noOptions())
        );
    }

    private function noOptions(): SpecificationOptions
    {
        return SpecificationOptions::fromMap([]);
    }
}
