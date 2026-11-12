<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\ProductValues;

use Amiut\ProductSpecs\Migration\MigrationState;
use Amiut\ProductSpecs\ProductValues\ProductValueWriter;
use Amiut\ProductSpecs\ProductValues\SpecificationsUpdate;
use Amiut\ProductSpecs\ProductValues\SpecificationsUpdater;
use Amiut\ProductSpecs\ProductValues\WordPressProductValueRepository;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionMapper;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use Amiut\ProductSpecs\Specification\Type\TextType;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetMapper;
use Amiut\ProductSpecs\SpecificationSet\WordPressSpecificationSetRepository;
use Amiut\ProductSpecs\Upgrade\DataVersion;
use Amiut\ProductSpecs\Upgrade\DataVersionRepository;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WP_Post;
use WP_Term;

final class SpecificationsUpdaterTest extends TestCase
{
    private const PRODUCT = 20;

    protected function setUp(): void
    {
        $GLOBALS['wp_test_post_types'] = [self::PRODUCT => 'product'];
        $GLOBALS['wp_test_options'] = [DataVersionRepository::OPTION => DataVersion::CURRENT_VERSION];
        $GLOBALS['wp_test_actions'] = [];
        $GLOBALS['wp_test_post_meta'] = [];
        $GLOBALS['wp_test_posts'] = [];

        $set = new WP_Post();
        $set->ID = 30;
        $set->post_type = 'specs-table';
        $set->post_name = 'laptops';
        $GLOBALS['wp_test_posts_by_id'] = [30 => $set];

        $term = new WP_Term();
        $term->term_id = 10;
        $term->name = 'Screen';
        $term->slug = 'screen';
        $GLOBALS['wp_test_terms'] = [10 => $term];
        $GLOBALS['wp_test_term_meta'] = [10 => ['attr_type' => 'text', 'attr_values' => [], 'attr_default' => '']];
    }

    public function testAnUpdateWithoutASetKeepsTheAssignedOne(): void
    {
        $this->storeProduct('laptops', ['screen' => '13 inch']);

        $saved = $this->updater()->save(
            SpecificationsUpdate::forProduct(self::PRODUCT)->withValues(['screen' => '15 inch'])
        );

        self::assertSame(['screen' => '15 inch'], $saved);
        self::assertSame('laptops', $this->repository()->assignedSet(self::PRODUCT));
    }

    public function testValuesThatWouldEndWithoutASetAreRefusedAndNothingIsWritten(): void
    {
        $this->storeProduct('laptops', ['screen' => '13 inch']);

        $this->assertRefused(
            SpecificationsUpdate::forProduct(self::PRODUCT)->withoutSet()->withValues(['screen' => '15 inch'])
        );
        self::assertSame('laptops', $this->repository()->assignedSet(self::PRODUCT));
        self::assertSame(['screen' => '13 inch'], $this->repository()->read(self::PRODUCT)->values());
    }

    public function testValuesForAProductWithNoSetAreRefused(): void
    {
        $this->assertRefused(SpecificationsUpdate::forProduct(self::PRODUCT)->withValues(['screen' => '15 inch']));
        self::assertSame([], $this->repository()->read(self::PRODUCT)->values());
    }

    public function testNothingIsWrittenBeforeTheMigrationIsComplete(): void
    {
        $GLOBALS['wp_test_options'] = [];

        $this->assertRefused(
            SpecificationsUpdate::forProduct(self::PRODUCT)->withSet('laptops')->withValues(['screen' => '15 inch'])
        );
        self::assertNull($this->repository()->assignedSet(self::PRODUCT));
        self::assertSame([], $this->repository()->read(self::PRODUCT)->values());
    }

    private function assertRefused(SpecificationsUpdate $update): void
    {
        try {
            $this->updater()->save($update);
            self::fail('The update was saved.');
        } catch (RuntimeException) {
            self::assertSame([], $GLOBALS['wp_test_actions']);
        }
    }

    /**
     * @param array<string, mixed> $values
     */
    private function storeProduct(string $setSlug, array $values): void
    {
        $GLOBALS['wp_test_post_meta'][self::PRODUCT] = [
            WordPressProductValueRepository::SET_META_KEY => $setSlug,
            WordPressProductValueRepository::VALUES_META_KEY => ['schema_version' => 1, 'values' => $values],
        ];
    }

    private function repository(): WordPressProductValueRepository
    {
        return new WordPressProductValueRepository();
    }

    private function updater(): SpecificationsUpdater
    {
        $writer = new ProductValueWriter(
            $this->repository(),
            new SpecificationDefinitionRepository(new SpecificationDefinitionMapper()),
            new SpecificationTypeRegistry(new TextType()),
            new WordPressSpecificationSetRepository(new SpecificationSetMapper())
        );

        return new SpecificationsUpdater(
            $writer,
            $this->repository(),
            new MigrationState(new DataVersionRepository())
        );
    }
}
