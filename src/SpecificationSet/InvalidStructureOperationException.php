<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet;

use RuntimeException;

final class InvalidStructureOperationException extends RuntimeException
{
    public static function duplicateGroupKey(string $key): self
    {
        return new self(
            sprintf('A group with the key "%s" is already in this set.', $key)
        );
    }

    public static function unknownGroup(string $key): self
    {
        return new self(
            sprintf('This set has no group with the key "%s".', $key)
        );
    }

    public static function duplicateItem(string $groupKey, int $termId): self
    {
        return new self(
            sprintf(
                'The specification %d is already in the group "%s".',
                $termId,
                $groupKey
            )
        );
    }

    public static function unknownItem(string $groupKey, int $termId): self
    {
        return new self(
            sprintf(
                'The group "%s" has no specification %d.',
                $groupKey,
                $termId
            )
        );
    }

    public static function positionOutOfRange(int $position, int $lastPosition): self
    {
        return new self(
            sprintf(
                'Position %d is outside the range 0 to %d.',
                $position,
                $lastPosition
            )
        );
    }
}
