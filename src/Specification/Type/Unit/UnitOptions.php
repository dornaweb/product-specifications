<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\Unit;

use Amiut\ProductSpecs\Specification\SpecificationOptions;

final class UnitOptions
{
    private const KIND = 'kind';
    private const UNITS = 'units';
    private const DEFAULT_UNIT = 'default_unit';
    private const RANGE = 'range';
    private const DECIMALS = 'decimals';
    private const MIN = 'min';
    private const MAX = 'max';
    private const MAX_DECIMALS = 4;
    private const DEFAULT_DECIMALS = 2;

    /**
     * @param array<string, string> $symbols Keyed by unit id.
     */
    private function __construct(
        private readonly array $symbols,
        private readonly string $defaultUnit,
        private readonly bool $range,
        private readonly int $decimals,
        private readonly Bounds $bounds
    ) {
    }

    public static function fromOptions(SpecificationOptions $options): self
    {
        $map = $options->toArray();
        $symbols = self::symbols($map[self::UNITS] ?? null);
        $default = $map[self::DEFAULT_UNIT] ?? null;
        $decimals = $map[self::DECIMALS] ?? null;

        return new self(
            $symbols,
            is_string($default) && isset($symbols[$default]) ? $default : (string) array_key_first($symbols),
            ($map[self::RANGE] ?? false) === true,
            is_int($decimals) ? $decimals : self::DEFAULT_DECIMALS,
            Bounds::fromStorage($map[self::MIN] ?? null, $map[self::MAX] ?? null)
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
            'required' => [self::UNITS],
            'properties' => [
                self::KIND => ['type' => 'string'],
                self::UNITS => [
                    'type' => 'array',
                    'minItems' => 1,
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
            ],
        ];
    }

    public function read(mixed $data): ?Measurement
    {
        return Measurement::fromData($data, $this->defaultUnit)?->rounded($this->decimals);
    }

    public function rejection(Measurement $measurement): ?string
    {
        $rejection = match (true) {
            !isset($this->symbols[$measurement->unit()]) => MeasurementRejection::UnitNotAllowed,
            $measurement->isRange() && !$this->range => MeasurementRejection::RangeNotAllowed,
            $measurement->isReversed() => MeasurementRejection::ReversedRange,
            default => null,
        };

        return $rejection?->message() ?? $this->bounds->rejection($measurement, $this->decimals);
    }

    /**
     * A unit not among the options is shown by its id.
     */
    public function format(Measurement $measurement): string
    {
        return $measurement->format($this->decimals, $this->symbols[$measurement->unit()] ?? $measurement->unit());
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
