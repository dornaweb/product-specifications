<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport;

use JsonException;

final class PackageReader
{
    public function __construct(private readonly SectionRegistry $sections)
    {
    }

    /**
     * Only a file that is not a package at all is rejected. An entry with a problem is kept
     * with its problems, so it can be skipped while the rest of the file is imported.
     *
     * @throws InvalidPackage
     */
    public function read(string $json): Package
    {
        $sections = $this->sections($json);
        $problems = [];

        foreach ($sections as $key => $entries) {
            if ($this->sections->has($key)) {
                $problems[$key] = $this->sections->get($key)->validate($entries);
            }
        }

        return $this->package($sections, $problems);
    }

    /**
     * Reads a file that was read and planned before. Its entries are not checked again: the
     * plan skips every entry that had a problem.
     *
     * @throws InvalidPackage
     */
    public function readPlanned(string $json): Package
    {
        return $this->package($this->sections($json));
    }

    /**
     * @return array<string, list<mixed>>
     * @throws InvalidPackage
     */
    private function sections(string $json): array
    {
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new InvalidPackage([__('The file is not valid JSON.', 'product-specifications')]);
        }

        if (!is_array($data) || ($data['format'] ?? null) !== PackageFormat::NAME) {
            throw new InvalidPackage([
                __('The file is not a Product Specifications package.', 'product-specifications'),
            ]);
        }

        $versionError = $this->versionError($data['schema_version'] ?? null);

        if ($versionError !== null) {
            throw new InvalidPackage([$versionError]);
        }

        $sections = $data['sections'] ?? null;

        if (!is_array($sections) || ($sections !== [] && array_is_list($sections))) {
            throw new InvalidPackage([Shape::error('sections', __('must be an object', 'product-specifications'))]);
        }

        $errors = [];
        $lists = [];

        foreach ($sections as $key => $entries) {
            if (!is_array($entries) || !array_is_list($entries)) {
                $errors[] = Shape::error((string) $key, __('must be a list', 'product-specifications'));
                continue;
            }

            $lists[(string) $key] = $entries;
        }

        if ($errors !== []) {
            throw new InvalidPackage($errors);
        }

        return $lists;
    }

    /**
     * @param array<string, list<mixed>> $sections
     * @param array<string, array<int, list<string>>> $problems
     */
    private function package(array $sections, array $problems = []): Package
    {
        $entries = [];

        foreach ($sections as $key => $list) {
            $entries[$key] = [];

            foreach ($list as $entry) {
                /** @var array<string, mixed> $object */
                $object = is_array($entry) ? $entry : [];
                $entries[$key][] = $object;
            }
        }

        return new Package($entries, $problems);
    }

    private function versionError(mixed $version): ?string
    {
        if (!is_int($version) || $version < 1) {
            return Shape::error('schema_version', __('must be a positive whole number', 'product-specifications'));
        }

        if ($version > PackageFormat::SCHEMA_VERSION) {
            return sprintf(
                /* translators: 1: the file's schema version, 2: the newest schema version this site reads. */
                __(
                    'The file uses package schema %1$d, made by a newer version of Product Specifications. This site reads up to schema %2$d. Update the plugin and try again.',
                    'product-specifications'
                ),
                $version,
                PackageFormat::SCHEMA_VERSION
            );
        }

        return null;
    }
}
