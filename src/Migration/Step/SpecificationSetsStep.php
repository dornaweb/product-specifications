<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration\Step;

use Amiut\ProductSpecs\Content\Taxonomy\LegacyGroupTaxonomy;
use Amiut\ProductSpecs\Migration\ChunkResult;
use Amiut\ProductSpecs\Migration\IdBatch;
use Amiut\ProductSpecs\Migration\MigrationStep;
use Amiut\ProductSpecs\Specification\SpecificationTaxonomy;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetMapper;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetPostType;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetStructure;
use Amiut\ProductSpecs\SpecificationSet\StructureMetaDefinition;
use WP_Error;
use WP_Term;

final class SpecificationSetsStep implements MigrationStep
{
    public const KEY = 'sets';

    private const GROUPS_META = '_groups';
    private const GROUP_META = 'attr_group';
    private const ARRANGEMENT_META = 'attributes';

    public function __construct(
        private readonly SetStructureBuilder $builder,
        private readonly SpecificationSetMapper $mapper
    ) {
    }

    public function key(): string
    {
        return self::KEY;
    }

    public function total(): int
    {
        return count(array_filter($this->setIds(), [$this, 'needsMigration']));
    }

    public function run(int $cursor, int $limit): ChunkResult
    {
        $batch = IdBatch::after($this->setIds(), $cursor, $limit);
        $processed = 0;
        $skipped = [];
        $errors = [];

        foreach ($batch->ids() as $setId) {
            if (!$this->hasLegacyGroups($setId)) {
                continue;
            }

            if ($this->hasStructure($setId)) {
                $skipped[] = $setId;
                continue;
            }

            $processed++;
            $structure = $this->structure($setId);

            if ($structure->groups() === []) {
                continue;
            }

            $stored = update_post_meta(
                $setId,
                StructureMetaDefinition::KEY,
                $this->mapper->structureToArray($structure)
            );

            if ($stored === false) {
                $errors[$setId] = __('The specification set could not be saved.', 'product-specifications');
                continue;
            }

            if (!$this->publish($setId)) {
                $errors[$setId] = __('The specification set could not be published.', 'product-specifications');
            }
        }

        return $batch->result($processed, $skipped, $errors);
    }

    // Only a published set renders, and a draft has no slug for its products to point at,
    // so every migrated table is published.
    private function publish(int $setId): bool
    {
        $status = get_post_status($setId);

        if ($status === 'publish' || $status === 'trash') {
            return true;
        }

        $updated = wp_update_post(['ID' => $setId, 'post_status' => 'publish'], true);

        return !$updated instanceof WP_Error && $updated > 0;
    }

    private function needsMigration(int $setId): bool
    {
        return $this->hasLegacyGroups($setId) && !$this->hasStructure($setId);
    }

    private function hasLegacyGroups(int $setId): bool
    {
        return $this->idList(get_post_meta($setId, self::GROUPS_META, true)) !== [];
    }

    private function hasStructure(int $setId): bool
    {
        $stored = get_post_meta($setId, StructureMetaDefinition::KEY, true);

        return $this->mapper->structureFromArray($stored)->groups() !== [];
    }

    private function structure(int $setId): SpecificationSetStructure
    {
        $groups = [];

        foreach ($this->idList(get_post_meta($setId, self::GROUPS_META, true)) as $groupId) {
            $group = get_term($groupId, LegacyGroupTaxonomy::KEY);

            if (!$group instanceof WP_Term) {
                continue;
            }

            $groups[] = $this->builder->group(
                $group->term_id,
                $group->name,
                $group->description,
                $this->idList(get_term_meta($group->term_id, self::ARRANGEMENT_META, true)),
                $this->members($group->term_id)
            );
        }

        return new SpecificationSetStructure(StructureMetaDefinition::CURRENT_VERSION, $groups);
    }

    /**
     * @return list<array{id: int, slug: string, name: string}>
     */
    private function members(int $groupId): array
    {
        $terms = get_terms([
            'taxonomy' => SpecificationTaxonomy::KEY,
            'hide_empty' => false,
            'meta_key' => self::GROUP_META,
            'meta_value' => (string) $groupId,
        ]);

        $members = [];

        foreach (is_array($terms) ? $terms : [] as $term) {
            if ($term instanceof WP_Term) {
                $members[] = ['id' => $term->term_id, 'slug' => $term->slug, 'name' => $term->name];
            }
        }

        return $members;
    }

    /**
     * @return list<int>
     */
    private function setIds(): array
    {
        $ids = get_posts([
            'post_type' => SpecificationSetPostType::KEY,
            'post_status' => array_values(get_post_stati()),
            'posts_per_page' => -1,
            'fields' => 'ids',
            'no_found_rows' => true,
        ]);

        return $this->idList($ids);
    }

    /**
     * @return list<int>
     */
    private function idList(mixed $stored): array
    {
        $ids = [];

        foreach (is_array($stored) ? $stored : [] as $id) {
            $id = is_numeric($id) ? (int) $id : 0;

            if ($id > 0 && !in_array($id, $ids, true)) {
                $ids[] = $id;
            }
        }

        return $ids;
    }
}
