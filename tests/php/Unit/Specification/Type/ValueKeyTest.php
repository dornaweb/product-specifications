<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Type;

use Amiut\ProductSpecs\Specification\Type\ValueKey;
use PHPUnit\Framework\TestCase;

/**
 * The editor reads the same cases, so a value both sides call one value is one value.
 */
final class ValueKeyTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function cases(): array
    {
        $contents = file_get_contents(dirname(__DIR__, 4) . '/fixtures/value-keys.json');
        $rows = json_decode(is_string($contents) ? $contents : '[]', true);
        $cases = [];

        foreach (is_array($rows) ? $rows : [] as $row) {
            $cases[(string) $row['label']] = [(string) $row['value'], (string) $row['key']];
        }

        return $cases;
    }

    /**
     * @dataProvider cases
     */
    public function testKey(string $value, string $key): void
    {
        static::assertSame($key, ValueKey::of($value));
    }
}
