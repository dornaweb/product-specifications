<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Deletion;

/**
 * The keys whose values are still being removed from products, in one option that is
 * never autoloaded. A queued key is not free for a new specification.
 */
final class PurgeQueue
{
    public const OPTION = 'product_specifications_purge_queue';

    /**
     * @return list<PurgeEntry>
     */
    public function entries(): array
    {
        $stored = get_option(self::OPTION, []);
        $entries = [];

        foreach (is_array($stored) ? $stored : [] as $item) {
            $entry = PurgeEntry::fromStorage($item);

            if ($entry !== null) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    public function holds(string $slug): bool
    {
        return $this->entry($slug) !== null;
    }

    /**
     * The first entry that has not failed.
     *
     * @phpstan-impure
     */
    public function pending(): ?PurgeEntry
    {
        foreach ($this->entries() as $entry) {
            if ($entry->isPending()) {
                return $entry;
            }
        }

        return null;
    }

    public function add(string $slug, int $cursor): void
    {
        $this->replace($slug, PurgeEntry::queued($slug, $cursor, time()));
    }

    public function advance(string $slug, int $cursor): void
    {
        $entry = $this->entry($slug);

        if ($entry !== null) {
            $this->replace($slug, $entry->at($cursor));
        }
    }

    public function recordFailure(string $slug, string $message): void
    {
        $entry = $this->entry($slug);

        if ($entry !== null) {
            $this->replace($slug, $entry->failed($message));
        }
    }

    public function remove(string $slug): void
    {
        $this->replace($slug, null);
    }

    public function retryAll(): void
    {
        $this->store(array_map(static fn (PurgeEntry $entry): PurgeEntry => $entry->retried(), $this->entries()));
    }

    private function entry(string $slug): ?PurgeEntry
    {
        foreach ($this->entries() as $entry) {
            if ($entry->slug() === $slug) {
                return $entry;
            }
        }

        return null;
    }

    private function replace(string $slug, ?PurgeEntry $replacement): void
    {
        $entries = [];
        $replaced = false;

        foreach ($this->entries() as $entry) {
            if ($entry->slug() !== $slug) {
                $entries[] = $entry;
                continue;
            }

            if ($replacement !== null) {
                $entries[] = $replacement;
            }

            $replaced = true;
        }

        if (!$replaced && $replacement !== null) {
            $entries[] = $replacement;
        }

        $this->store($entries);
    }

    /**
     * @param list<PurgeEntry> $entries
     */
    private function store(array $entries): void
    {
        if ($entries === []) {
            delete_option(self::OPTION);

            return;
        }

        update_option(
            self::OPTION,
            array_map(static fn (PurgeEntry $entry): array => $entry->toStorage(), $entries),
            false
        );
    }
}
