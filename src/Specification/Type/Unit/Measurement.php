<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\Unit;

final class Measurement
{
    private const VALUE = 'value';
    private const MIN = 'min';
    private const MAX = 'max';
    private const UNIT = 'unit';

    private function __construct(
        private readonly float $from,
        private readonly ?float $to,
        private readonly string $unit
    ) {
    }

    public static function isEmpty(mixed $data): bool
    {
        if (!is_array($data)) {
            return $data === null || $data === '';
        }

        foreach ([self::VALUE, self::MIN, self::MAX] as $key) {
            if (!in_array($data[$key] ?? null, [null, ''], true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Reads `{value, unit}` or `{min, max, unit}`. Without a unit, the default unit is assumed.
     * Null when there is no number.
     */
    public static function fromData(mixed $data, string $defaultUnit): ?self
    {
        return is_array($data) ? self::fromMap($data, $defaultUnit) : null;
    }

    public function unit(): string
    {
        return $this->unit;
    }

    public function isRange(): bool
    {
        return $this->to !== null;
    }

    public function isReversed(): bool
    {
        return $this->to !== null && $this->from > $this->to;
    }

    public function lowest(): float
    {
        return min($this->from, $this->to ?? $this->from);
    }

    public function highest(): float
    {
        return max($this->from, $this->to ?? $this->from);
    }

    public function rounded(int $decimals): self
    {
        return self::create(
            round($this->from, $decimals),
            $this->to === null ? null : round($this->to, $decimals),
            $this->unit
        );
    }

    /**
     * @return array{value: int|float, unit: string}|array{min: int|float, max: int|float, unit: string}
     */
    public function toStorage(): array
    {
        if ($this->to === null) {
            return [self::VALUE => self::stored($this->from), self::UNIT => $this->unit];
        }

        return [
            self::MIN => self::stored($this->from),
            self::MAX => self::stored($this->to),
            self::UNIT => $this->unit,
        ];
    }

    public function format(int $decimals, string $symbol): string
    {
        $number = DecimalNumber::format($this->from, $decimals);

        if ($this->to !== null) {
            $number .= '–' . DecimalNumber::format($this->to, $decimals);
        }

        return $symbol === '' ? $number : "{$number} {$symbol}";
    }

    private static function create(float $from, ?float $to, string $unit): self
    {
        return new self($from, $to === $from ? null : $to, $unit);
    }

    /**
     * @param array<array-key, mixed> $map
     */
    private static function fromMap(array $map, string $defaultUnit): ?self
    {
        $unit = is_string($map[self::UNIT] ?? null) ? sanitize_text_field($map[self::UNIT]) : '';
        $unit = $unit !== '' ? $unit : $defaultUnit;

        if (array_key_exists(self::VALUE, $map)) {
            $value = DecimalNumber::parse($map[self::VALUE]);

            return $value === null ? null : self::create($value, null, $unit);
        }

        $min = DecimalNumber::parse($map[self::MIN] ?? null);
        $max = DecimalNumber::parse($map[self::MAX] ?? null);
        $only = $min ?? $max;

        if ($min === null || $max === null) {
            return $only === null ? null : self::create($only, null, $unit);
        }

        return self::create($min, $max, $unit);
    }

    private static function stored(float $number): int|float
    {
        return floor($number) === $number && abs($number) < PHP_INT_MAX ? (int) $number : $number;
    }
}
