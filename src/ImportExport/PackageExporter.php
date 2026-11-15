<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport;

/**
 * Writes the package as it reads it, entry by entry, so memory stays flat however large
 * the catalogue is.
 */
final class PackageExporter
{
    private const JSON_FLAGS = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

    public function __construct(
        private readonly SectionRegistry $sections,
        private readonly string $pluginVersion
    ) {
    }

    /**
     * @param list<string> $sectionKeys
     * @param callable(string): void $write
     * @return array<string, int> The number of entries written per section.
     */
    public function export(array $sectionKeys, callable $write): array
    {
        $header = [
            'format' => PackageFormat::NAME,
            'schema_version' => PackageFormat::SCHEMA_VERSION,
            'plugin_version' => $this->pluginVersion,
            'exported_at' => gmdate('c'),
            'source' => home_url(),
        ];

        $write(substr($this->encode($header), 0, -1) . ',"sections":{');

        $counts = [];

        foreach ($this->sections->ordered() as $section) {
            if (!in_array($section->key(), $sectionKeys, true)) {
                continue;
            }

            $write(($counts === [] ? '' : ',') . $this->encode($section->key()) . ':[');
            $counts[$section->key()] = 0;

            foreach ($section->export() as $entry) {
                $write(($counts[$section->key()] === 0 ? '' : ',') . $this->encode($entry));
                $counts[$section->key()]++;
            }

            $write(']');
        }

        $write('}}');

        return $counts;
    }

    private function encode(mixed $value): string
    {
        return (string) wp_json_encode($value, self::JSON_FLAGS);
    }
}
