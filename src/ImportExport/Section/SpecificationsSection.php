<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport\Section;

use Amiut\ProductSpecs\ImportExport\EntryOutcome;
use Amiut\ProductSpecs\ImportExport\ImportPlan;
use Amiut\ProductSpecs\ImportExport\Section;
use Amiut\ProductSpecs\ImportExport\Shape;
use Amiut\ProductSpecs\ProductValues\ProductValueRepository;
use Amiut\ProductSpecs\Specification\SpecificationDefinition;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\SpecificationOptions;
use Amiut\ProductSpecs\Specification\Type\OptionsChecker;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use WP_Error;

final class SpecificationsSection implements Section
{
    public const KEY = 'specifications';

    public function __construct(
        private readonly SpecificationDefinitionRepository $definitions,
        private readonly SpecificationTypeRegistry $types,
        private readonly OptionsChecker $options,
        private readonly ProductValueRepository $values
    ) {
    }

    public function key(): string
    {
        return self::KEY;
    }

    public function dependsOn(): array
    {
        return [];
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
                ...Shape::text($entry, 'name', $path, true),
                ...Shape::text($entry, 'type', $path, true),
                ...Shape::text($entry, 'description', $path, false),
            ];

            if (array_key_exists('options', $entry) && !is_array($entry['options'])) {
                $entryErrors[] = Shape::error("{$path}.options", __('must be an object', 'product-specifications'));
            }

            $slug = Shape::string($entry, 'slug');

            if ($entryErrors === [] && isset($seen[$slug])) {
                $entryErrors[] = Shape::duplicate("{$path}.slug");
            }

            $seen[$slug] = true;

            if ($entryErrors !== []) {
                $errors[$index] = $entryErrors;
            }
        }

        return $errors;
    }

    public function export(): iterable
    {
        foreach ($this->definitions->findAll() as $definition) {
            yield [
                'slug' => $definition->key(),
                'name' => $definition->name(),
                'description' => $definition->description(),
                'type' => $definition->typeId(),
                'options' => $definition->options()->toArray(),
                'default' => $this->types->has($definition->typeId())
                    ? $this->types->get($definition->typeId())->sanitize($definition->defaultValue())
                    : $definition->defaultValue(),
            ];
        }
    }

    public function planEntry(array $entry, ImportPlan $plan): EntryOutcome
    {
        $slug = Shape::string($entry, 'slug');
        $incoming = $this->incoming($entry);

        if (is_string($incoming)) {
            return EntryOutcome::skip($slug, $incoming);
        }

        $existing = $this->definitions->findByKey($slug);

        if ($existing === null) {
            return EntryOutcome::create($slug);
        }

        if ($existing->typeId() !== $incoming->typeId()) {
            return EntryOutcome::conflict(
                $slug,
                sprintf(
                    /* translators: 1: the specification's type on this store, 2: its type in the file. */
                    __(
                        'On this store it is a "%1$s" specification, and the file has "%2$s". It is left as it is, and the file\'s set items and values for it are skipped.',
                        'product-specifications'
                    ),
                    $existing->typeId(),
                    $incoming->typeId()
                )
            );
        }

        if ($this->sameAs($existing, $incoming)) {
            return EntryOutcome::unchanged($slug);
        }

        return EntryOutcome::update($slug, $this->updateNotes($existing, $incoming));
    }

    public function importEntry(array $entry, ImportPlan $plan): EntryOutcome
    {
        $slug = Shape::string($entry, 'slug');
        $incoming = $this->incoming($entry);

        if (is_string($incoming)) {
            return EntryOutcome::failed($slug, $incoming);
        }

        $existing = $this->definitions->findByKey($slug);
        $definition = $existing === null
            ? $incoming
            : SpecificationDefinition::fromStorage(
                (int) $existing->id(),
                $incoming->name(),
                $existing->key(),
                $incoming->description(),
                $incoming->typeId(),
                $incoming->options(),
                $incoming->defaultValue()
            );

        if ($this->definitions->save($definition) < 1) {
            return EntryOutcome::failed($slug, __('The specification could not be saved.', 'product-specifications'));
        }

        return $existing === null ? EntryOutcome::create($slug) : EntryOutcome::update($slug);
    }

    /**
     * @param array<string, mixed> $entry
     * @return SpecificationDefinition|string The definition the entry describes, or why it cannot be used.
     */
    private function incoming(array $entry): SpecificationDefinition|string
    {
        $typeId = Shape::string($entry, 'type');

        if (!$this->types->has($typeId)) {
            return sprintf(
                /* translators: %s: a specification type id. */
                __('The type "%s" is not available on this store.', 'product-specifications'),
                $typeId
            );
        }

        $options = $this->options->check($typeId, $entry['options'] ?? [], 'options');

        if ($options instanceof WP_Error) {
            return sprintf(
                /* translators: %s: why the options were rejected. */
                __('Its options are not valid: %s', 'product-specifications'),
                $options->get_error_message()
            );
        }

        return SpecificationDefinition::create(
            Shape::string($entry, 'name'),
            Shape::string($entry, 'slug'),
            is_string($entry['description'] ?? null) ? $entry['description'] : '',
            $typeId,
            SpecificationOptions::fromMap($options),
            $this->types->get($typeId)->sanitize($entry['default'] ?? null)
        );
    }

    private function sameAs(SpecificationDefinition $existing, SpecificationDefinition $incoming): bool
    {
        $type = $this->types->get($incoming->typeId());

        return $existing->name() === $incoming->name()
            && $existing->description() === $incoming->description()
            && $existing->options()->toArray() === $incoming->options()->toArray()
            && $type->sanitize($existing->defaultValue()) === $incoming->defaultValue();
    }

    /**
     * @return list<string>
     */
    private function updateNotes(SpecificationDefinition $existing, SpecificationDefinition $incoming): array
    {
        $notes = [];

        if ($existing->name() !== $incoming->name()) {
            $notes[] = sprintf(
                /* translators: 1: the name on this store, 2: the name in the file. */
                __('Renamed from "%1$s" to "%2$s".', 'product-specifications'),
                $existing->name(),
                $incoming->name()
            );
        }

        $removed = array_values(array_diff($existing->options()->choices(), $incoming->options()->choices()));
        $affected = $removed === [] ? 0 : $this->values->countUsing($existing->key(), $removed);

        if ($affected > 0) {
            $notes[] = sprintf(
                /* translators: 1: number of products, 2: comma-separated choices. */
                _n(
                    '%1$d product uses a choice the file removes: %2$s.',
                    '%1$d products use choices the file removes: %2$s.',
                    $affected,
                    'product-specifications'
                ),
                $affected,
                implode(', ', $removed)
            );
        }

        return $notes;
    }
}
