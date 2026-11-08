<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport\Section;

use Amiut\ProductSpecs\ImportExport\EntryOutcome;
use Amiut\ProductSpecs\ImportExport\ImportPlan;
use Amiut\ProductSpecs\ImportExport\ObjectMatcher;
use Amiut\ProductSpecs\ImportExport\Section;
use Amiut\ProductSpecs\ImportExport\Shape;
use Amiut\ProductSpecs\ProductValues\ProductValueRepository;
use Amiut\ProductSpecs\ProductValues\ProductValueWriter;
use Amiut\ProductSpecs\ProductValues\WordPressProductValueRepository;
use Amiut\ProductSpecs\Specification\SpecificationDefinition;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetRepository;
use RuntimeException;

final class ValuesSection implements Section
{
    public const KEY = 'values';

    private const BATCH = 100;
    private const EXPORTED_STATUSES = ['publish', 'draft', 'pending', 'private', 'future'];

    /**
     * @var array<string, ObjectMatcher>
     */
    private array $matchers = [];

    /**
     * @param list<ObjectMatcher> $matchers
     */
    public function __construct(
        private readonly ProductValueRepository $values,
        private readonly ProductValueWriter $writer,
        private readonly SpecificationDefinitionRepository $definitions,
        private readonly SpecificationSetRepository $sets,
        private readonly SpecificationTypeRegistry $types,
        array $matchers
    ) {
        foreach ($matchers as $matcher) {
            $this->matchers[$matcher->type()] = $matcher;
        }
    }

    public function key(): string
    {
        return self::KEY;
    }

    public function dependsOn(): array
    {
        return [SpecificationsSection::KEY, SetsSection::KEY];
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
                ...$this->objectErrors($entry['object'] ?? null, "{$path}.object"),
                ...$this->valueErrors($entry, $path),
            ];

            if (array_key_exists('set', $entry) && $entry['set'] !== null) {
                $entryErrors = [...$entryErrors, ...Shape::identifier($entry, 'set', $path, Shape::SLUG_PATTERN)];
            }

            if ($entryErrors === []) {
                foreach ($this->identities($entry) as $identity) {
                    if (isset($seen[$identity])) {
                        $entryErrors[] = Shape::duplicate("{$path}.object");
                        break;
                    }
                }

                foreach ($this->identities($entry) as $identity) {
                    $seen[$identity] = true;
                }
            }

            if ($entryErrors !== []) {
                $errors[$index] = $entryErrors;
            }
        }

        return $errors;
    }

    public function export(): iterable
    {
        $matcher = $this->matchers['product'] ?? null;

        if ($matcher === null) {
            return;
        }

        $definitions = $this->definitionsByKey();
        $page = 1;

        do {
            $productIds = $this->productIds($page++);

            foreach ($productIds as $productId) {
                $values = $this->exportedValues($this->values->read($productId)->values(), $definitions);
                $set = $this->values->assignedSet($productId);

                if ($values === [] && $set === null) {
                    continue;
                }

                yield [
                    'object' => $matcher->identify($productId),
                    'set' => $set,
                    'values' => (object) $values,
                ];
            }
        } while (count($productIds) === self::BATCH);
    }

    public function planEntry(array $entry, ImportPlan $plan): EntryOutcome
    {
        $object = $this->object($entry);
        $matcher = $this->matchers[Shape::string($object, 'type')] ?? null;

        if ($matcher === null) {
            return EntryOutcome::skip(
                Shape::string($object, 'type'),
                sprintf(
                    /* translators: %s: an object type, such as "product". */
                    __('Values for objects of type "%s" cannot be imported on this store.', 'product-specifications'),
                    Shape::string($object, 'type')
                )
            );
        }

        $label = $matcher->describe($object);
        $objectId = $matcher->find($object);

        if ($objectId === null) {
            return EntryOutcome::skip($label, __('No product on this store matches it.', 'product-specifications'));
        }

        $notes = [];
        $setChanges = $this->plannedSet($entry, $plan, $objectId, $notes);
        $valueChanges = $this->plannedValues($entry, $plan, $objectId, $notes);

        if (!$setChanges && !$valueChanges) {
            return EntryOutcome::unchanged($label, $notes);
        }

        return EntryOutcome::update($label, $notes);
    }

    public function importEntry(array $entry, ImportPlan $plan): EntryOutcome
    {
        $object = $this->object($entry);
        $matcher = $this->matchers[Shape::string($object, 'type')] ?? null;
        $label = $matcher === null ? Shape::string($object, 'type') : $matcher->describe($object);
        $objectId = $matcher?->find($object);

        if ($objectId === null) {
            return EntryOutcome::failed($label, __('No product on this store matches it.', 'product-specifications'));
        }

        $values = array_filter(
            $this->entryValues($entry),
            fn (string $key): bool => $plan->outcomeFor(SpecificationsSection::KEY, $key)?->keepsItem() !== false,
            ARRAY_FILTER_USE_KEY
        );
        $requested = $entry['set'] ?? null;
        $set = is_string($requested) ? $this->storedSetSlug($requested) : null;

        try {
            $this->writer->merge($objectId, $set, $values);
        } catch (RuntimeException $exception) {
            return EntryOutcome::failed($label, $exception->getMessage());
        }

        return EntryOutcome::update($label);
    }

    /**
     * @param array<string, mixed> $entry
     * @param list<string> $notes
     */
    private function plannedSet(array $entry, ImportPlan $plan, int $objectId, array &$notes): bool
    {
        $requested = $entry['set'] ?? null;

        if (!is_string($requested)) {
            return false;
        }

        $set = $this->plannedSetSlug($entry, $plan);

        if ($set === null) {
            $notes[] = sprintf(
                /* translators: %s: a set slug. */
                __('The set "%s" is not in the file or on this store, so the product keeps its set.', 'product-specifications'),
                urldecode($requested)
            );

            return false;
        }

        return $this->values->assignedSet($objectId) !== $set;
    }

    /**
     * @param array<string, mixed> $entry
     * @param list<string> $notes
     */
    private function plannedValues(array $entry, ImportPlan $plan, int $objectId, array &$notes): bool
    {
        $current = $this->values->read($objectId)->values();
        $changes = false;

        foreach ($this->entryValues($entry) as $key => $value) {
            $outcome = $plan->outcomeFor(SpecificationsSection::KEY, $key);

            if ($outcome !== null && !$outcome->keepsItem()) {
                $notes[] = sprintf(
                    /* translators: %s: a specification slug. */
                    __('Skips the value for "%s": the file\'s specification is not imported.', 'product-specifications'),
                    urldecode($key)
                );
                continue;
            }

            $definition = $this->definitions->findByKey($key);

            if ($definition === null) {
                if ($outcome === null) {
                    $notes[] = sprintf(
                        /* translators: %s: a specification slug. */
                        __('Skips the value for "%s": no specification has that slug.', 'product-specifications'),
                        urldecode($key)
                    );
                    continue;
                }

                $changes = true;
                continue;
            }

            $sanitized = $this->types->has($definition->typeId())
                ? $this->types->get($definition->typeId())->sanitize($value, $definition->options())
                : null;

            if ($sanitized === null) {
                $notes[] = sprintf(
                    /* translators: %s: a specification slug. */
                    __('Skips the value for "%s": the specification\'s type does not accept it.', 'product-specifications'),
                    urldecode($key)
                );
                continue;
            }

            if (!array_key_exists($definition->key(), $current) || $current[$definition->key()] !== $sanitized) {
                $changes = true;
            }
        }

        return $changes;
    }

    /**
     * @param array<string, mixed> $entry
     */
    private function plannedSetSlug(array $entry, ImportPlan $plan): ?string
    {
        $requested = $entry['set'] ?? null;

        if (!is_string($requested)) {
            return null;
        }

        $stored = $this->storedSetSlug($requested);

        if ($stored !== null) {
            return $stored;
        }

        return $plan->outcomeFor(SetsSection::KEY, $requested)?->keepsItem() === true ? $requested : null;
    }

    private function storedSetSlug(string $slug): ?string
    {
        $set = $this->sets->bySlug($slug);

        if ($set === null) {
            return null;
        }

        return $set->slug() !== '' ? $set->slug() : $slug;
    }

    /**
     * @param array<string, mixed> $values
     * @param array<string, SpecificationDefinition> $definitions
     * @return array<string, mixed>
     */
    private function exportedValues(array $values, array $definitions): array
    {
        $exported = [];

        foreach ($values as $key => $value) {
            $definition = $definitions[$key] ?? null;

            if ($definition === null) {
                continue;
            }

            $readable = $this->types->has($definition->typeId())
                ? $this->types->get($definition->typeId())->sanitize($value, $definition->options())
                : $value;

            if ($readable !== null) {
                $exported[$key] = $readable;
            }
        }

        return $exported;
    }

    /**
     * @return array<string, SpecificationDefinition>
     */
    private function definitionsByKey(): array
    {
        $definitions = [];

        foreach ($this->definitions->findAll() as $definition) {
            $definitions[$definition->key()] = $definition;
        }

        return $definitions;
    }

    /**
     * @return list<int>
     */
    private function productIds(int $page): array
    {
        $ids = get_posts([
            'post_type' => 'product',
            'post_status' => self::EXPORTED_STATUSES,
            'posts_per_page' => self::BATCH,
            'paged' => $page,
            'orderby' => 'ID',
            'order' => 'ASC',
            'fields' => 'ids',
            'no_found_rows' => true,
            'meta_query' => [
                'relation' => 'OR',
                ['key' => WordPressProductValueRepository::VALUES_META_KEY, 'compare' => 'EXISTS'],
                ['key' => WordPressProductValueRepository::SET_META_KEY, 'compare' => 'EXISTS'],
            ],
        ]);

        return array_values(array_map('intval', is_array($ids) ? $ids : []));
    }

    /**
     * @return list<string>
     */
    private function objectErrors(mixed $object, string $path): array
    {
        if (!is_array($object) || ($object !== [] && array_is_list($object))) {
            return [Shape::error($path, __('must be an object', 'product-specifications'))];
        }

        $errors = [
            ...Shape::text($object, 'type', $path, true),
            ...Shape::text($object, 'sku', $path, false),
            ...Shape::text($object, 'slug', $path, false),
        ];

        if ($errors === [] && Shape::string($object, 'sku') === '' && Shape::string($object, 'slug') === '') {
            $errors[] = Shape::error($path, __('needs a sku or a slug', 'product-specifications'));
        }

        return $errors;
    }

    /**
     * @param array<array-key, mixed> $entry
     * @return list<string>
     */
    private function valueErrors(array $entry, string $path): array
    {
        $values = $entry['values'] ?? null;

        if (!is_array($values) || ($values !== [] && array_is_list($values))) {
            return [Shape::error("{$path}.values", __('must be an object', 'product-specifications'))];
        }

        $errors = [];

        foreach (array_keys($values) as $key) {
            if (preg_match(Shape::SLUG_PATTERN, (string) $key) !== 1) {
                $errors[] = Shape::error(
                    "{$path}.values.{$key}",
                    __('is not a specification slug', 'product-specifications')
                );
            }
        }

        return $errors;
    }

    /**
     * @param array<array-key, mixed> $entry
     * @return list<string>
     */
    private function identities(array $entry): array
    {
        $object = is_array($entry['object'] ?? null) ? $entry['object'] : [];
        $type = Shape::string($object, 'type');

        return array_values(array_filter([
            Shape::string($object, 'sku') === '' ? '' : "{$type}:sku:" . Shape::string($object, 'sku'),
            Shape::string($object, 'slug') === '' ? '' : "{$type}:slug:" . Shape::string($object, 'slug'),
        ]));
    }

    /**
     * @param array<string, mixed> $entry
     * @return array<string, mixed>
     */
    private function object(array $entry): array
    {
        /** @var array<string, mixed> $object */
        $object = is_array($entry['object'] ?? null) ? $entry['object'] : [];

        return $object;
    }

    /**
     * @param array<string, mixed> $entry
     * @return array<string, mixed>
     */
    private function entryValues(array $entry): array
    {
        $values = [];

        foreach (is_array($entry['values'] ?? null) ? $entry['values'] : [] as $key => $value) {
            $values[(string) $key] = $value;
        }

        return $values;
    }
}
