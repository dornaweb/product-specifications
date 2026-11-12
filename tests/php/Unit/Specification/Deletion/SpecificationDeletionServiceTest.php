<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Deletion;

use Amiut\ProductSpecs\Specification\Deletion\SpecificationDeletionService;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionMapper;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\SpecificationTaxonomy;
use Amiut\ProductSpecs\SpecificationSet\SpecificationGroup;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSet;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetItem;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetRepository;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetStructure;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WP_Term;

final class SpecificationDeletionServiceTest extends TestCase
{
    private const TERM_ID = 10;
    private const SLUG = 'screen-a1b2c3d4';

    protected function setUp(): void
    {
        $term = new WP_Term();
        $term->term_id = self::TERM_ID;
        $term->name = 'Screen';
        $term->slug = self::SLUG;
        $GLOBALS['wp_test_terms'] = [self::TERM_ID => $term];
        $GLOBALS['wp_test_term_meta'] = [
            self::TERM_ID => [
                'attr_type' => 'text',
                'attr_values' => [],
                'attr_default' => '',
            ],
        ];
    }

    public function testStripsTheItemOnlyFromSetsThatHoldIt(): void
    {
        $withItem = $this->setWithItem(1);
        $withoutItem = SpecificationSet::fromStorage(2, 'Set 2', 'publish', SpecificationSetStructure::empty());

        $sets = $this->sets([$withItem, $withoutItem]);

        $this->service($sets)->beforeDelete(self::TERM_ID, SpecificationTaxonomy::KEY);

        self::assertCount(1, $sets->saved);
        self::assertSame([], $sets->saved[0]->structure()->groups()[0]->items());
    }

    public function testRevertsAlreadyCleanedSetsAndNamesTheSetFailureWhenALaterSetFailsToSave(): void
    {
        $sets = $this->sets([$this->setWithItem(1), $this->setWithItem(2)]);
        $sets->failOnNthSave = 2;

        try {
            $this->service($sets)->beforeDelete(self::TERM_ID, SpecificationTaxonomy::KEY);
            self::fail('Expected a RuntimeException.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('specification sets could not be updated', $exception->getMessage());
        }

        // Set 1 cleaned, set 2's failed attempt, then set 1 reverted to its original structure.
        self::assertCount(3, $sets->saved);
        self::assertSame([], $sets->saved[0]->structure()->groups()[0]->items());
        self::assertSame(
            self::SLUG,
            $sets->saved[2]->structure()->groups()[0]->items()[0]->slug()
        );
    }

    private function service(SpecificationSetRepository $sets): SpecificationDeletionService
    {
        return new SpecificationDeletionService(
            new SpecificationDefinitionRepository(new SpecificationDefinitionMapper()),
            $sets
        );
    }

    private function setWithItem(int $id): SpecificationSet
    {
        return SpecificationSet::fromStorage(
            $id,
            "Set {$id}",
            'publish',
            new SpecificationSetStructure(1, [
                new SpecificationGroup('group', 'Group', '', true, [
                    SpecificationSetItem::create(self::TERM_ID, self::SLUG),
                ]),
            ])
        );
    }

    /**
     * @param list<SpecificationSet> $all
     */
    private function sets(array $all): object
    {
        return new class ($all) implements SpecificationSetRepository {
            /** @var list<SpecificationSet> */
            public array $saved = [];
            public int $failOnNthSave = 0;

            public function __construct(private array $all)
            {
            }

            public function byId(int $id): ?SpecificationSet
            {
                return null;
            }

            public function bySlug(string $slug): ?SpecificationSet
            {
                return null;
            }

            public function all(array $args = []): array
            {
                return $this->all;
            }

            public function save(SpecificationSet $set): int
            {
                $this->saved[] = $set;

                return count($this->saved) === $this->failOnNthSave ? 0 : 1;
            }

            public function delete(int $id): bool
            {
                return true;
            }
        };
    }
}
