<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification;

use Amiut\ProductSpecs\Specification\Deletion\PurgeQueue;
use Amiut\ProductSpecs\Specification\SlugAvailability;
use Amiut\ProductSpecs\Specification\SpecificationSlug;
use PHPUnit\Framework\TestCase;
use WP_Term;

final class SlugAvailabilityTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['wp_test_options'] = [];
        $GLOBALS['wp_test_terms'] = [];

        $this->term(1, 'weight');
        $this->term(2, 'weight-2');
        (new PurgeQueue())->add('colour', 40);
    }

    public function testAKeyIsTakenByATermOrByAPurgeStillRunning(): void
    {
        $availability = new SlugAvailability(new PurgeQueue());

        self::assertTrue($availability->taken('weight'));
        self::assertTrue($availability->taken('colour'));
        self::assertFalse($availability->taken('battery'));
    }

    public function testAFreeKeyIsKeptAndATakenOneGetsTheFirstFreeNumber(): void
    {
        $availability = new SlugAvailability(new PurgeQueue());

        self::assertSame('battery', $availability->free(SpecificationSlug::preserved('battery'))->value());
        self::assertSame('weight-3', $availability->free(SpecificationSlug::preserved('weight'))->value());
        self::assertSame('colour-2', $availability->free(SpecificationSlug::preserved('colour'))->value());
    }

    public function testAWholeNumberIsNeverKeptAsItIs(): void
    {
        self::assertSame(
            '2024-2',
            (new SlugAvailability(new PurgeQueue()))->free(SpecificationSlug::fromName('2024'))->value()
        );
    }

    private function term(int $id, string $slug): void
    {
        $term = new WP_Term();
        $term->term_id = $id;
        $term->slug = $slug;
        $term->name = ucfirst($slug);
        $GLOBALS['wp_test_terms'][$id] = $term;
    }
}
