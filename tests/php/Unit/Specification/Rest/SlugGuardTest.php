<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Rest;

use Amiut\ProductSpecs\Migration\MigrationState;
use Amiut\ProductSpecs\Specification\Deletion\PurgeQueue;
use Amiut\ProductSpecs\Specification\Rest\SlugGuard;
use Amiut\ProductSpecs\Specification\SlugAvailability;
use Amiut\ProductSpecs\Specification\SpecificationTaxonomy;
use Amiut\ProductSpecs\Upgrade\DataVersion;
use Amiut\ProductSpecs\Upgrade\DataVersionRepository;
use PHPUnit\Framework\TestCase;
use WP_Error;
use WP_REST_Request;
use WP_Term;

final class SlugGuardTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['wp_test_options'] = [DataVersionRepository::OPTION => DataVersion::CURRENT_VERSION];

        $term = new WP_Term();
        $term->term_id = 7;
        $term->slug = 'weight';
        $term->name = 'Weight';
        $GLOBALS['wp_test_terms'] = [7 => $term];
    }

    public function testACreateKeepsTheClientKeySanitized(): void
    {
        $term = (object) ['name' => 'Screen size', 'slug' => ' Screen Size '];

        self::assertSame('screen-size', $this->guard()->choose($term, new WP_REST_Request())->slug);
    }

    public function testACreateWithoutAKeyDerivesAFreeOneFromTheName(): void
    {
        $guard = $this->guard();

        self::assertSame('weight-2', $guard->choose((object) ['name' => 'Weight'], new WP_REST_Request())->slug);
        self::assertSame('battery', $guard->choose((object) ['name' => 'Battery'], new WP_REST_Request())->slug);
    }

    public function testAKeyThatCannotBeStoredIsLeftForTheRefusalToName(): void
    {
        $term = (object) ['name' => 'Year', 'slug' => '2024'];

        self::assertSame('2024', $this->guard()->choose($term, new WP_REST_Request())->slug);
    }

    public function testAnUpdateNeverCarriesASlug(): void
    {
        $term = (object) ['name' => 'Weight', 'slug' => 'something-else'];

        self::assertObjectNotHasProperty('slug', $this->guard()->choose($term, new WP_REST_Request(['id' => 7])));
    }

    public function testATakenKeyIsRefusedWithASuggestion(): void
    {
        $refused = $this->guard()->refuse('Weight', SpecificationTaxonomy::KEY, ['slug' => 'weight']);

        self::assertInstanceOf(WP_Error::class, $refused);
        self::assertSame(SlugGuard::TAKEN, $refused->get_error_code());
        self::assertSame(['status' => 409, 'suggestion' => 'weight-2'], $refused->get_error_data());
    }

    public function testAWholeNumberKeyIsRefused(): void
    {
        $refused = $this->guard()->refuse('Year', SpecificationTaxonomy::KEY, ['slug' => '2024']);

        self::assertInstanceOf(WP_Error::class, $refused);
        self::assertSame(SlugGuard::INVALID, $refused->get_error_code());
    }

    public function testAFreeKeyAnotherTaxonomyOrNoKeyPassesThrough(): void
    {
        $guard = $this->guard();

        self::assertSame('Battery', $guard->refuse('Battery', SpecificationTaxonomy::KEY, ['slug' => 'battery']));
        self::assertSame('Weight', $guard->refuse('Weight', 'category', ['slug' => 'weight']));
        self::assertSame('Weight', $guard->refuse('Weight', SpecificationTaxonomy::KEY, []));
    }

    public function testTheSlugIsFrozenOnceTheSiteIsMigrated(): void
    {
        $data = ['name' => 'Mass', 'slug' => 'mass'];

        self::assertSame(
            ['name' => 'Mass', 'slug' => 'weight'],
            $this->guard()->freeze($data, 7, SpecificationTaxonomy::KEY)
        );
        self::assertSame($data, $this->guard()->freeze($data, 7, 'category'));
    }

    public function testTheMigrationMayStillRewriteASlug(): void
    {
        $GLOBALS['wp_test_options'] = [DataVersionRepository::OPTION => DataVersion::LEGACY_VERSION];
        $data = ['slug' => 'mass'];

        self::assertSame($data, $this->guard()->freeze($data, 7, SpecificationTaxonomy::KEY));
    }

    private function guard(): SlugGuard
    {
        return new SlugGuard(
            new SlugAvailability(new PurgeQueue()),
            new MigrationState(new DataVersionRepository())
        );
    }
}
