<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Migration\Step;

use Amiut\ProductSpecs\Migration\Step\WordPressLegacyProducts;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use wpdb;

final class WordPressLegacyProductsTest extends TestCase
{
    public function testAFailedQueryForTheNextProductsIsNotReadAsNoProductsLeft(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('MySQL server has gone away');

        $this->products()->idsAfter(0, 101);
    }

    public function testAFailedCountIsNotReadAsNothingPending(): void
    {
        $this->expectException(RuntimeException::class);

        $this->products()->pending();
    }

    protected function setUp(): void
    {
        $database = new wpdb();
        $database->last_error = 'MySQL server has gone away';
        $GLOBALS['wpdb'] = $database; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['wpdb']);
    }

    private function products(): WordPressLegacyProducts
    {
        return new WordPressLegacyProducts();
    }
}
