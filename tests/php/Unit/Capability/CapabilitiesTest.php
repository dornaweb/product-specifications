<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Capability;

use Amiut\ProductSpecs\Capability\Capabilities;
use PHPUnit\Framework\TestCase;

final class CapabilitiesTest extends TestCase
{
    /**
     * @return array<string, array{mixed}>
     */
    public static function unusableFilterValueProvider(): array
    {
        return [
            'empty string' => [''],
            'null' => [null],
            'array of capabilities' => [['manage_woocommerce']],
            'boolean from a filter that forgot to return' => [true],
        ];
    }

    /**
     * An unusable filter return must not become the gate. An empty capability
     * denies every user, administrators included.
     *
     * @dataProvider unusableFilterValueProvider
     */
    public function testUnusableFilterValueFallsBackToTheDefault(mixed $value): void
    {
        static::assertSame(
            Capabilities::DEFAULT_CAPABILITY,
            Capabilities::fromFilterValue($value)->manageSpecifications()
        );
    }

    public function testFilterCanReplaceTheCapability(): void
    {
        static::assertSame(
            'manage_options',
            Capabilities::fromFilterValue('manage_options')->manageSpecifications()
        );
    }
}
