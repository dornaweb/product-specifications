<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Comparison;

use Amiut\ProductSpecs\Block\StylesheetSettings;
use Amiut\ProductSpecs\Comparison\CompareButton;
use Amiut\ProductSpecs\Comparison\ComparisonSettings;
use Amiut\ProductSpecs\Comparison\ProductCatalog;
use Amiut\ProductSpecs\Integration\WooCommerce\ProductTabSettings;
use Amiut\ProductSpecs\Migration\MigrationState;
use Amiut\ProductSpecs\ProductValues\WordPressProductValueRepository;
use Amiut\ProductSpecs\Settings\SettingsProviders;
use Amiut\ProductSpecs\Settings\SettingsRepository;
use Amiut\ProductSpecs\Settings\SettingsSchema;
use Amiut\ProductSpecs\SpecificationSet\SpecificationGroup;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSet;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetItem;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetPresentation;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetStructure;
use Amiut\ProductSpecs\Tests\Unit\ImportExport\InMemorySetRepository;
use Amiut\ProductSpecs\Upgrade\DataVersionRepository;
use PHPUnit\Framework\TestCase;
use WC_Product;
use WC_Product_Variation;

final class CompareButtonTest extends TestCase
{
    private const PAGE = 5;
    public const HIDDEN = 14;

    protected function setUp(): void
    {
        $GLOBALS['wp_test_options'] = [
            DataVersionRepository::OPTION => 2,
            SettingsRepository::OPTION_NAME => ['compare_page' => self::PAGE],
        ];
        $GLOBALS['wp_test_post_statuses'] = [self::PAGE => 'publish'];
        $GLOBALS['wp_test_post_meta'] = [];

        $this->product(10, 'phones', ['weight' => '180 g']);
        $this->product(11, 'phones', []);
        $this->product(12, 'legacy', ['weight' => '2 kg']);
        $this->product(13, 'drafts', ['weight' => '1 kg']);
        $this->product(self::HIDDEN, 'phones', ['weight' => '150 g']);
    }

    public function testAProductWhoseSetOffersComparisonLinksToTheComparePage(): void
    {
        self::assertSame('https://shop.test/?page_id=5&compare=10', $this->button()->url(new WC_Product(10)));
        self::assertSame('https://shop.test/?page_id=5&compare=11', $this->button()->url(new WC_Product(11)));
    }

    /**
     * @dataProvider withoutButton
     */
    public function testNoButtonWhenTheComparisonCannotOpen(callable $arrange, WC_Product $product): void
    {
        $arrange();

        self::assertNull($this->button()->url($product));
        self::assertSame('', $this->button()->render($product));
    }

    /**
     * @return array<string, array{callable, WC_Product}>
     */
    public static function withoutButton(): array
    {
        $nothing = static function (): void {
        };

        return [
            'no compare page selected' => [
                static function (): void {
                    $GLOBALS['wp_test_options'][SettingsRepository::OPTION_NAME] = [];
                },
                new WC_Product(10),
            ],
            'the compare page is a draft' => [
                static function (): void {
                    $GLOBALS['wp_test_post_statuses'][self::PAGE] = 'draft';
                },
                new WC_Product(10),
            ],
            'a variation' => [$nothing, new WC_Product_Variation(10, 9)],
            'a set without comparison' => [$nothing, new WC_Product(12)],
            'a draft set' => [$nothing, new WC_Product(13)],
            'a product the catalog does not compare' => [$nothing, new WC_Product(self::HIDDEN)],
        ];
    }

    private function button(): CompareButton
    {
        $structure = new SpecificationSetStructure(1, [
            new SpecificationGroup('general', 'General', '', true, [SpecificationSetItem::create('weight')]),
        ]);
        $sets = new InMemorySetRepository([
            'phones' => SpecificationSet::fromStorage(
                1,
                'Phones',
                'publish',
                $structure,
                'phones',
                '',
                SpecificationSetPresentation::forNewSet()
            ),
            'legacy' => SpecificationSet::fromStorage(2, 'Legacy', 'publish', $structure, 'legacy'),
            'drafts' => SpecificationSet::fromStorage(
                3,
                'Drafts',
                'draft',
                $structure,
                'drafts',
                '',
                SpecificationSetPresentation::forNewSet()
            ),
        ]);
        $providers = (new SettingsProviders())
            ->with(new ProductTabSettings())
            ->with(new StylesheetSettings())
            ->with(new ComparisonSettings());

        return new CompareButton(
            new SettingsRepository(new SettingsSchema($providers)),
            new WordPressProductValueRepository(),
            $sets,
            new class implements ProductCatalog {
                public function comparableProducts(array $ids): array
                {
                    return [];
                }

                public function searchInSet(string $setSlug, string $search, array $exclude, int $limit): array
                {
                    return [];
                }

                public function isComparable(WC_Product $product): bool
                {
                    return $product->get_id() !== CompareButtonTest::HIDDEN;
                }
            },
            new MigrationState(new DataVersionRepository())
        );
    }

    /**
     * @param array<string, string> $values
     */
    private function product(int $id, string $set, array $values): void
    {
        $GLOBALS['wp_test_post_meta'][$id] = [
            WordPressProductValueRepository::SET_META_KEY => $set,
            WordPressProductValueRepository::VALUES_META_KEY => ['schema_version' => 1, 'values' => $values],
        ];
    }
}
