<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport;

final class Package
{
    /**
     * @param array<string, list<array<string, mixed>>> $sections
     * @param array<string, array<int, list<string>>> $problems
     */
    public function __construct(private readonly array $sections, private readonly array $problems = [])
    {
    }

    /**
     * @return list<string>
     */
    public function sectionKeys(): array
    {
        return array_keys($this->sections);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function entries(string $sectionKey): array
    {
        return $this->sections[$sectionKey] ?? [];
    }

    /**
     * @return list<string>
     */
    public function problems(string $sectionKey, int $index): array
    {
        return $this->problems[$sectionKey][$index] ?? [];
    }
}
