<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification;

use Amiut\ProductSpecs\Specification\SpecificationSlug;
use PHPUnit\Framework\TestCase;

final class SpecificationSlugTest extends TestCase
{
    public function testSlugForALongNameFitsTheSlugColumn(): void
    {
        $slug = SpecificationSlug::forName(str_repeat('screen size ', 40))->value();

        self::assertLessThanOrEqual(200, strlen($slug));
        self::assertMatchesRegularExpression('/^screen-size-.*-[0-9a-f]{8}$/', $slug);
    }
}
