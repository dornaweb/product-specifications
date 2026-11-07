<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration\Step;

use Amiut\ProductSpecs\SpecificationSet\SpecificationGroup;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetItem;

final class SetStructureBuilder
{
    /**
     * Members named in the arrangement come first, in its order. The rest follow
     * by name.
     *
     * @param list<int> $arrangement
     * @param list<array{id: int, slug: string, name: string}> $members
     */
    public function group(
        int $groupId,
        string $name,
        string $description,
        array $arrangement,
        array $members
    ): SpecificationGroup {
        usort(
            $members,
            static function (array $first, array $second) use ($arrangement): int {
                $firstPosition = array_search($first['id'], $arrangement, true);
                $secondPosition = array_search($second['id'], $arrangement, true);

                if ($firstPosition === false && $secondPosition === false) {
                    return $first['name'] <=> $second['name'];
                }

                if ($firstPosition === false) {
                    return 1;
                }

                if ($secondPosition === false) {
                    return -1;
                }

                return $firstPosition <=> $secondPosition;
            }
        );

        return new SpecificationGroup(
            'group-' . $groupId,
            $name,
            $description,
            true,
            array_map(
                static fn (array $member): SpecificationSetItem => SpecificationSetItem::create(
                    $member['id'],
                    $member['slug']
                ),
                $members
            )
        );
    }
}
