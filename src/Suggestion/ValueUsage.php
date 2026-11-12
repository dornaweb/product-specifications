<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Suggestion;

use Amiut\ProductSpecs\Specification\Type\TextOptions;
use Amiut\ProductSpecs\Specification\Type\ValueKey;

/**
 * How many products carry each value of one specification, by the value's key and
 * by each spelling of it.
 *
 * @phpstan-type Suggestion array{value: string, count: int, learned: bool}
 */
final class ValueUsage
{
    public const SCHEMA_VERSION = 1;
    public const LEARNED_LIMIT = 500;

    /**
     * @param array<string, array<string, int>> $counts
     */
    private function __construct(private readonly array $counts)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public static function fromStorage(mixed $stored): self
    {
        $values = is_array($stored) ? ($stored['values'] ?? null) : null;

        if (!is_array($values)) {
            return self::empty();
        }

        $counts = [];

        foreach ($values as $key => $spellings) {
            if (!is_array($spellings)) {
                continue;
            }

            foreach ($spellings as $spelling => $count) {
                if (is_int($count) && $count > 0) {
                    $counts[(string) $key][(string) $spelling] = $count;
                }
            }
        }

        return new self($counts);
    }

    /**
     * @return array{schema_version: int, values: array<string, array<string, int>>}
     */
    public function toStorage(): array
    {
        return [
            'schema_version' => self::SCHEMA_VERSION,
            'values' => $this->counts,
        ];
    }

    /**
     * One product's value changed from the first to the second. Either may be absent.
     */
    public function withChange(mixed $old, mixed $new): self
    {
        $counts = $this->counts;

        foreach (self::spellingsByKey($old) as $key => $spelling) {
            $remaining = ($counts[$key][$spelling] ?? 0) - 1;
            unset($counts[$key][$spelling]);

            if ($remaining > 0) {
                $counts[$key][$spelling] = $remaining;
            }

            if (($counts[$key] ?? []) === []) {
                unset($counts[$key]);
            }
        }

        foreach (self::spellingsByKey($new) as $key => $spelling) {
            $counts[$key][$spelling] = ($counts[$key][$spelling] ?? 0) + 1;
        }

        return new self($counts);
    }

    /**
     * Most carried first. On a tie a typed suggestion comes first, typed suggestions
     * keep their order, and the rest is alphabetical.
     *
     * @return list<Suggestion>
     */
    public function suggestions(TextOptions $options): array
    {
        $typed = [];

        foreach ($options->suggestions() as $suggestion) {
            $key = ValueKey::of($suggestion);
            $typed[$key] ??= $suggestion;
        }

        $ranked = [];

        foreach (array_keys($typed) as $position => $key) {
            $ranked[] = [$this->total((string) $key), $position, $typed[$key], false];
        }

        if ($options->learns()) {
            array_push($ranked, ...$this->learned($typed));
        }

        usort(
            $ranked,
            static fn (array $left, array $right): int => [$right[0], $left[1], $left[2]]
                <=> [$left[0], $right[1], $right[2]]
        );

        return array_map(
            static fn (array $entry): array => ['value' => $entry[2], 'count' => $entry[0], 'learned' => $entry[3]],
            $ranked
        );
    }

    /**
     * @param array<string, string> $typed
     * @return list<array{int, int, string, bool}>
     */
    private function learned(array $typed): array
    {
        $learned = [];

        foreach (array_keys(array_diff_key($this->counts, $typed)) as $key) {
            $learned[] = [$this->total((string) $key), PHP_INT_MAX, $this->spelling((string) $key), true];
        }

        usort(
            $learned,
            static fn (array $left, array $right): int => [$right[0], $left[2]] <=> [$left[0], $right[2]]
        );

        return array_slice($learned, 0, self::LEARNED_LIMIT);
    }

    private function total(string $key): int
    {
        return array_sum($this->counts[$key] ?? []);
    }

    private function spelling(string $key): string
    {
        $spellings = $this->counts[$key] ?? [];
        $best = '';
        $bestCount = 0;

        foreach ($spellings as $spelling => $count) {
            $spelling = (string) $spelling;

            if ($count > $bestCount || ($count === $bestCount && strcmp($spelling, $best) < 0)) {
                $best = $spelling;
                $bestCount = $count;
            }
        }

        return $best;
    }

    /**
     * A product is counted once per value, under the first spelling it uses.
     *
     * @return array<string, string>
     */
    private static function spellingsByKey(mixed $value): array
    {
        $items = is_array($value) ? $value : [$value];
        $spellings = [];

        foreach ($items as $item) {
            if (!is_string($item)) {
                continue;
            }

            $key = ValueKey::of($item);

            if ($key !== '') {
                $spellings[$key] ??= $item;
            }
        }

        return $spellings;
    }
}
