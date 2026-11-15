<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Type;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use Amiut\ProductSpecs\Specification\Type\SelectType;
use PHPUnit\Framework\TestCase;

final class SelectTypeTest extends TestCase
{
    /**
     * @return array<string, array{mixed, bool}>
     */
    public static function inputs(): array
    {
        return [
            'one of the choices' => ['Blue', true],
            'empty' => ['', true],
            'nothing' => [null, true],
            'not a choice' => ['Green', false],
            'a choice stored with extra spaces' => ['Size XL', true],
        ];
    }

    /**
     * @dataProvider inputs
     */
    public function testOnlyAChoiceOrNothingIsAccepted(mixed $input, bool $accepted): void
    {
        $options = SpecificationOptions::fromMap(['values' => ['Red', 'Blue', ' Size  XL']]);

        self::assertSame($accepted, (new SelectType())->validate($input, $options) === null);
    }
}
