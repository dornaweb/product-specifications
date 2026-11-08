<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport\Section;

use Amiut\ProductSpecs\ImportExport\EntryOutcome;
use Amiut\ProductSpecs\ImportExport\ImportPlan;
use Amiut\ProductSpecs\ImportExport\Section;
use Amiut\ProductSpecs\ImportExport\Shape;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\SpecificationSet\SpecificationGroup;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSet;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetItem;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetRepository;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetStructure;
use Amiut\ProductSpecs\SpecificationSet\StructureMetaDefinition;

final class SetsSection implements Section
{
    public const KEY = 'sets';

    private const STATUSES = ['publish', 'draft', 'pending', 'private'];

    public function __construct(
        private readonly SpecificationSetRepository $sets,
        private readonly SpecificationDefinitionRepository $definitions
    ) {
    }

    public function key(): string
    {
        return self::KEY;
    }

    public function dependsOn(): array
    {
        return [SpecificationsSection::KEY];
    }

    public function validate(array $entries): array
    {
        $errors = [];
        $seen = [];

        foreach ($entries as $index => $entry) {
            $path = "[{$index}]";

            if (!is_array($entry)) {
                $errors[$index] = [Shape::error($path, __('must be an object', 'product-specifications'))];
                continue;
            }

            $entryErrors = [
                ...Shape::identifier($entry, 'slug', $path, Shape::SLUG_PATTERN),
                ...Shape::text($entry, 'title', $path, false),
                ...Shape::text($entry, 'description', $path, false),
                ...Shape::list($entry, 'groups', $path, true),
            ];

            if (array_key_exists('status', $entry) && !in_array($entry['status'], self::STATUSES, true)) {
                $entryErrors[] = Shape::error(
                    "{$path}.status",
                    sprintf(
                        /* translators: %s: comma-separated list of allowed statuses. */
                        __('must be one of: %s', 'product-specifications'),
                        implode(', ', self::STATUSES)
                    )
                );
            }

            if ($entryErrors === []) {
                /** @var list<mixed> $groups */
                $groups = $entry['groups'];
                $entryErrors = $this->groupErrors($groups, "{$path}.groups");

                $slug = Shape::string($entry, 'slug');

                if (isset($seen[$slug])) {
                    $entryErrors[] = Shape::duplicate("{$path}.slug");
                }

                $seen[$slug] = true;
            }

            if ($entryErrors !== []) {
                $errors[$index] = $entryErrors;
            }
        }

        return $errors;
    }

    public function export(): iterable
    {
        foreach ($this->sets->all() as $set) {
            if (!in_array($set->status(), self::STATUSES, true)) {
                continue;
            }

            yield [
                'slug' => $set->slug(),
                'title' => $set->title(),
                'description' => $set->description(),
                'status' => $set->status(),
                'groups' => array_map(
                    static fn (SpecificationGroup $group): array => [
                        'key' => $group->key(),
                        'label' => $group->label(),
                        'description' => $group->description(),
                        'visible' => $group->visible(),
                        'items' => array_map(
                            static fn (SpecificationSetItem $item): array => [
                                'specification' => $item->slug(),
                                'visible' => $item->visible(),
                                'comparable' => $item->comparable(),
                            ],
                            $group->items()
                        ),
                    ],
                    $set->structure()->groups()
                ),
            ];
        }
    }

    public function planEntry(array $entry, ImportPlan $plan): EntryOutcome
    {
        $slug = Shape::string($entry, 'slug');
        $notes = [];

        foreach ($this->itemSlugs($entry) as $specification) {
            $reason = $this->unavailable($specification, $plan);

            if ($reason !== null) {
                $notes[] = sprintf(
                    /* translators: 1: a specification slug, 2: why it cannot be placed. */
                    __('Leaves out "%1$s": %2$s', 'product-specifications'),
                    $specification,
                    $reason
                );
            }
        }

        $existing = $this->sets->bySlug($slug);

        if ($existing === null) {
            return EntryOutcome::create($slug, $notes);
        }

        return $this->changes($existing, $entry, $plan)
            ? EntryOutcome::update($slug, $notes)
            : EntryOutcome::unchanged($slug, $notes);
    }

    public function importEntry(array $entry, ImportPlan $plan): EntryOutcome
    {
        $slug = Shape::string($entry, 'slug');
        $incoming = $this->structure($entry, $plan);
        $title = Shape::string($entry, 'title');
        $status = is_string($entry['status'] ?? null) ? $entry['status'] : 'publish';
        $description = is_string($entry['description'] ?? null) ? $entry['description'] : '';
        $existing = $this->sets->bySlug($slug);

        $set = $existing === null
            ? SpecificationSet::create($title, $status, $incoming, $slug, $description)
            : SpecificationSet::fromStorage(
                (int) $existing->id(),
                $title,
                $status,
                $existing->structure()->mergedWith($incoming),
                $existing->slug(),
                $description
            );

        if ($this->sets->save($set) < 1) {
            return EntryOutcome::failed(
                $slug,
                __('The specification set could not be saved.', 'product-specifications')
            );
        }

        return $existing === null ? EntryOutcome::create($slug) : EntryOutcome::update($slug);
    }

    /**
     * A specification the file creates is not on this store yet, so no set here holds it.
     *
     * @param array<string, mixed> $entry
     */
    private function changes(SpecificationSet $existing, array $entry, ImportPlan $plan): bool
    {
        foreach ($this->itemSlugs($entry) as $specification) {
            if ($plan->outcomeFor(SpecificationsSection::KEY, $specification)?->status() === EntryOutcome::CREATE) {
                return true;
            }
        }

        $status = is_string($entry['status'] ?? null) ? $entry['status'] : 'publish';
        $description = is_string($entry['description'] ?? null) ? $entry['description'] : '';

        return $existing->title() !== Shape::string($entry, 'title')
            || $existing->status() !== $status
            || $existing->description() !== $description
            || $existing->structure()->mergedWith($this->structure($entry, $plan)) != $existing->structure();
    }

    /**
     * @param list<mixed> $groups
     * @return list<string>
     */
    private function groupErrors(array $groups, string $path): array
    {
        $errors = [];
        $keys = [];
        $specifications = [];

        foreach ($groups as $index => $group) {
            $groupPath = "{$path}[{$index}]";

            if (!is_array($group)) {
                $errors[] = Shape::error($groupPath, __('must be an object', 'product-specifications'));
                continue;
            }

            $groupErrors = [
                ...Shape::identifier($group, 'key', $groupPath, Shape::KEY_PATTERN),
                ...Shape::text($group, 'label', $groupPath, true),
                ...Shape::text($group, 'description', $groupPath, false),
                ...Shape::boolean($group, 'visible', $groupPath),
                ...Shape::list($group, 'items', $groupPath, true),
            ];

            if ($groupErrors !== []) {
                array_push($errors, ...$groupErrors);
                continue;
            }

            $key = Shape::string($group, 'key');

            if (isset($keys[$key])) {
                $errors[] = Shape::duplicate("{$groupPath}.key");
            }

            $keys[$key] = true;
            $items = is_array($group['items']) ? $group['items'] : [];

            foreach (array_values($items) as $itemIndex => $item) {
                $itemPath = "{$groupPath}.items[{$itemIndex}]";

                if (!is_array($item)) {
                    $errors[] = Shape::error($itemPath, __('must be an object', 'product-specifications'));
                    continue;
                }

                $itemErrors = [
                    ...Shape::identifier($item, 'specification', $itemPath, Shape::SLUG_PATTERN),
                    ...Shape::boolean($item, 'visible', $itemPath),
                    ...Shape::boolean($item, 'comparable', $itemPath),
                ];

                $specification = Shape::string($item, 'specification');

                if ($itemErrors === [] && isset($specifications[$specification])) {
                    $itemErrors[] = Shape::duplicate("{$itemPath}.specification");
                }

                if ($itemErrors === []) {
                    $specifications[$specification] = true;
                }

                array_push($errors, ...$itemErrors);
            }
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $entry
     * @return list<string>
     */
    private function itemSlugs(array $entry): array
    {
        $slugs = [];

        foreach ($this->groupEntries($entry) as $group) {
            foreach ($this->itemEntries($group) as $item) {
                $slugs[] = Shape::string($item, 'specification');
            }
        }

        return $slugs;
    }

    /**
     * @param array<string, mixed> $entry
     */
    private function structure(array $entry, ImportPlan $plan): SpecificationSetStructure
    {
        $groups = [];

        foreach ($this->groupEntries($entry) as $group) {
            $items = [];

            foreach ($this->itemEntries($group) as $item) {
                $specification = Shape::string($item, 'specification');
                $definition = $this->unavailable($specification, $plan) === null
                    ? $this->definitions->findByKey($specification)
                    : null;

                if ($definition !== null && $definition->id() !== null) {
                    $items[] = SpecificationSetItem::create(
                        $definition->id(),
                        $definition->key(),
                        ($item['visible'] ?? true) !== false,
                        ($item['comparable'] ?? false) === true
                    );
                }
            }

            $groups[] = new SpecificationGroup(
                Shape::string($group, 'key'),
                Shape::string($group, 'label'),
                is_string($group['description'] ?? null) ? $group['description'] : '',
                ($group['visible'] ?? true) !== false,
                $items
            );
        }

        return new SpecificationSetStructure(StructureMetaDefinition::CURRENT_VERSION, $groups);
    }

    /**
     * A specification the file skips or conflicts with is unavailable, and so is one
     * found neither in the file nor on this store.
     */
    private function unavailable(string $specification, ImportPlan $plan): ?string
    {
        $outcome = $plan->outcomeFor(SpecificationsSection::KEY, $specification);

        if ($outcome !== null) {
            return $outcome->keepsItem()
                ? null
                : __('the file\'s specification was not imported.', 'product-specifications');
        }

        return $this->definitions->findByKey($specification) === null
            ? __('no specification has that slug in the file or on this store.', 'product-specifications')
            : null;
    }

    /**
     * @param array<string, mixed> $entry
     * @return list<array<string, mixed>>
     */
    private function groupEntries(array $entry): array
    {
        /** @var list<array<string, mixed>> $groups */
        $groups = is_array($entry['groups'] ?? null) ? $entry['groups'] : [];

        return $groups;
    }

    /**
     * @param array<string, mixed> $group
     * @return list<array<string, mixed>>
     */
    private function itemEntries(array $group): array
    {
        /** @var list<array<string, mixed>> $items */
        $items = is_array($group['items'] ?? null) ? $group['items'] : [];

        return $items;
    }
}
