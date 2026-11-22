<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Comparison;

final class ComparedProductIds
{
    public const QUERY_PARAMETER = 'compare';

    private const MAXIMUM_READ = 50;

    /**
     * @param list<int> $ids
     */
    private function __construct(private readonly array $ids)
    {
    }

    public static function fromQuery(mixed $raw): self
    {
        $ids = [];

        foreach (is_string($raw) ? explode(',', $raw) : [] as $token) {
            $token = trim($token);

            if (ctype_digit($token) && (int) $token > 0) {
                $ids[] = (int) $token;
            }
        }

        return self::of($ids);
    }

    /**
     * @param list<int> $ids
     */
    public static function of(array $ids): self
    {
        return new self(array_slice(array_values(array_unique($ids)), 0, self::MAXIMUM_READ));
    }

    /**
     * @return list<int>
     */
    public function ids(): array
    {
        return $this->ids;
    }

    public function isEmpty(): bool
    {
        return $this->ids === [];
    }

    public function without(int $id): self
    {
        return new self(array_values(array_filter($this->ids, static fn (int $kept): bool => $kept !== $id)));
    }

    public function toQuery(): string
    {
        return implode(',', $this->ids);
    }
}
