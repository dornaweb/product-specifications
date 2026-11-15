<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Rest;

use Amiut\ProductSpecs\Specification\Rest\SpecificationCollectionQuery;
use PHPUnit\Framework\TestCase;
use WP_REST_Request;

final class SpecificationCollectionQueryTest extends TestCase
{
    public function testATypeNarrowsTheTermQuery(): void
    {
        $query = new SpecificationCollectionQuery();
        $request = new WP_REST_Request([SpecificationCollectionQuery::TYPE_PARAM => 'select']);

        static::assertSame(
            [
                [
                    'key' => 'attr_type',
                    'value' => 'select',
                    'compare' => '=',
                ],
            ],
            $query->query(['hide_empty' => false], $request)['meta_query']
        );
    }

    public function testAnEmptyTypeLeavesTheQueryAlone(): void
    {
        $query = new SpecificationCollectionQuery();

        static::assertSame(
            ['hide_empty' => false],
            $query->query(['hide_empty' => false], new WP_REST_Request())
        );
    }
}
