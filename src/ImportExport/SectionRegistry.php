<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport;

use LogicException;

final class SectionRegistry
{
    public const REGISTER_ACTION = 'product_specifications_register_package_sections';

    /**
     * @var array<string, Section>
     */
    private array $sections = [];

    public function register(Section $section): void
    {
        $this->sections[$section->key()] = $section;
    }

    public function has(string $key): bool
    {
        return isset($this->sections[$key]);
    }

    public function get(string $key): Section
    {
        if (!$this->has($key)) {
            throw new LogicException(sprintf('No package section is registered as "%s".', $key));
        }

        return $this->sections[$key];
    }

    /**
     * @return list<string> In dependency order.
     */
    public function keys(): array
    {
        return array_map(static fn (Section $section): string => $section->key(), $this->ordered());
    }

    /**
     * A dependency that is not registered does not hold a section back.
     *
     * @return list<Section>
     */
    public function ordered(): array
    {
        $ordered = [];
        $pending = $this->sections;

        while ($pending !== []) {
            $ready = array_filter(
                $pending,
                fn (Section $section): bool => $this->dependenciesPlaced($section, $ordered)
            );

            if ($ready === []) {
                throw new LogicException('Package sections depend on each other in a cycle.');
            }

            foreach ($ready as $key => $section) {
                $ordered[$key] = $section;
                unset($pending[$key]);
            }
        }

        return array_values($ordered);
    }

    /**
     * @param array<string, Section> $ordered
     */
    private function dependenciesPlaced(Section $section, array $ordered): bool
    {
        foreach ($section->dependsOn() as $dependency) {
            if ($this->has($dependency) && !isset($ordered[$dependency])) {
                return false;
            }
        }

        return true;
    }
}
