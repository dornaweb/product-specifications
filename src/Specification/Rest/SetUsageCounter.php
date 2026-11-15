<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Rest;

use Amiut\ProductSpecs\SpecificationSet\SpecificationSetRepository;

/**
 * How many sets compose a specification, counted from the set structures. Sets
 * are tens of documents; the products holding a value are not counted here and
 * are not counted in any list.
 */
final class SetUsageCounter
{
    /**
     * @var array<string, int>|null
     */
    private ?array $counts = null;

    public function __construct(private readonly SpecificationSetRepository $sets)
    {
    }

    public function forSlug(string $slug): int
    {
        return $this->counts()[$slug] ?? 0;
    }

    /**
     * @return array<string, int>
     */
    private function counts(): array
    {
        if (is_array($this->counts)) {
            return $this->counts;
        }

        $counts = [];

        foreach ($this->sets->all() as $set) {
            $seen = [];

            foreach ($set->structure()->groups() as $group) {
                foreach ($group->items() as $item) {
                    $seen[$item->slug()] = true;
                }
            }

            foreach (array_keys($seen) as $slug) {
                $counts[$slug] = ($counts[$slug] ?? 0) + 1;
            }
        }

        $this->counts = $counts;

        return $counts;
    }
}
