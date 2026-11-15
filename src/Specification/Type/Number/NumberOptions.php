<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\Number;

use Amiut\ProductSpecs\Specification\SpecificationOptions;

final class NumberOptions
{
    private const KIND = 'kind';
    private const UNITS = 'units';
    private const DEFAULT_UNIT = 'default_unit';
    private const RANGE = 'range';
    private const DECIMALS = 'decimals';
    private const MIN = 'min';
    private const MAX = 'max';
    private const THOUSANDS = 'thousands';
    private const MAX_DECIMALS = 4;
    private const DEFAULT_DECIMALS = 0;

    /**
     * @param array<string, string> $symbols Keyed by unit id.
     */
    private function __construct(
        private readonly array $symbols,
        private readonly string $defaultUnit,
        private readonly bool $range,
        private readonly int $decimals,
        private readonly Bounds $bounds,
        private readonly NumberFormat $numberFormat
    ) {
    }

    public static function fromOptions(SpecificationOptions $options): self
    {
        $map = $options->toArray();
        $symbols = self::symbols($map[self::UNITS] ?? null);
        $default = $map[self::DEFAULT_UNIT] ?? null;
        $decimals = is_int($map[self::DECIMALS] ?? null) ? $map[self::DECIMALS] : self::DEFAULT_DECIMALS;

        return new self(
            $symbols,
            is_string($default) && isset($symbols[$default]) ? $default : (string) array_key_first($symbols),
            ($map[self::RANGE] ?? false) === true,
            $decimals,
            Bounds::fromStorage($map[self::MIN] ?? null, $map[self::MAX] ?? null),
            new NumberFormat($decimals, ($map[self::THOUSANDS] ?? true) !== false)
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function schema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                self::KIND => ['type' => 'string'],
                self::UNITS => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['id', 'symbol'],
                        'properties' => [
                            'id' => ['type' => 'string', 'minLength' => 1],
                            'symbol' => ['type' => 'string', 'minLength' => 1],
                        ],
                    ],
                ],
                self::DEFAULT_UNIT => ['type' => 'string'],
                self::RANGE => ['type' => 'boolean'],
                self::DECIMALS => ['type' => 'integer', 'minimum' => 0, 'maximum' => self::MAX_DECIMALS],
                self::MIN => ['type' => ['number', 'null']],
                self::MAX => ['type' => ['number', 'null']],
                self::THOUSANDS => ['type' => 'boolean'],
            ],
        ];
    }

    public function read(mixed $data): ?NumberValue
    {
        return NumberValue::fromData($data, $this->defaultUnit)?->rounded($this->decimals);
    }

    public function rejection(NumberValue $number): ?string
    {
        $rejection = match (true) {
            $number->unit() !== '' && !isset($this->symbols[$number->unit()])
                => NumberRejection::UnitNotAllowed,
            $number->isRange() && !$this->range => NumberRejection::RangeNotAllowed,
            $number->isReversed() => NumberRejection::ReversedRange,
            default => null,
        };

        return $rejection?->message() ?? $this->bounds->rejection($number, $this->numberFormat);
    }

    /**
     * A unit not among the options is shown by its id.
     */
    public function format(NumberValue $number): string
    {
        return $number->format($this->numberFormat, $this->symbols[$number->unit()] ?? $number->unit());
    }

    /**
     * @return array<string, string>
     */
    private static function symbols(mixed $units): array
    {
        $symbols = [];

        foreach (is_array($units) ? $units : [] as $unit) {
            $id = is_array($unit) ? ($unit['id'] ?? null) : null;
            $symbol = is_array($unit) ? ($unit['symbol'] ?? null) : null;

            if (is_string($id) && is_string($symbol)) {
                $symbols[$id] = $symbol;
            }
        }

        return $symbols;
    }
}
