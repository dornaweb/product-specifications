<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\Number;

final class Bounds
{
    public function __construct(
        private readonly ?float $min,
        private readonly ?float $max
    ) {
    }

    /**
     * The lower of two bounds is the minimum, whichever key holds it.
     */
    public static function fromStorage(mixed $min, mixed $max): self
    {
        $min = self::bound($min);
        $max = self::bound($max);

        return $min !== null && $max !== null && $min > $max ? new self($max, $min) : new self($min, $max);
    }

    public function rejection(NumberValue $number, NumberFormat $format): ?string
    {
        $tooLow = $this->min !== null && $number->lowest() < $this->min;
        $tooHigh = $this->max !== null && $number->highest() > $this->max;

        if (!$tooLow && !$tooHigh) {
            return null;
        }

        return match (true) {
            $this->min !== null && $this->max !== null => sprintf(
                /* translators: 1: the lowest allowed number, 2: the highest allowed number. */
                __('Enter a number from %1$s to %2$s.', 'product-specifications'),
                $format->format($this->min),
                $format->format($this->max)
            ),
            $this->min !== null => sprintf(
                /* translators: %s: the lowest allowed number. */
                __('Enter a number of at least %s.', 'product-specifications'),
                $format->format($this->min)
            ),
            default => sprintf(
                /* translators: %s: the highest allowed number. */
                __('Enter a number of at most %s.', 'product-specifications'),
                $format->format((float) $this->max)
            ),
        };
    }

    private static function bound(mixed $value): ?float
    {
        return is_int($value) || is_float($value) ? (float) $value : null;
    }
}
