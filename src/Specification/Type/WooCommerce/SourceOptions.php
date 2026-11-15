<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\WooCommerce;

use Amiut\ProductSpecs\Specification\SpecificationOptions;

final class SourceOptions
{
    private const SOURCE = 'source';
    private const NAME = 'name';

    private function __construct(private readonly string $source, private readonly string $name)
    {
    }

    public static function fromOptions(SpecificationOptions $options): self
    {
        $map = $options->toArray();
        $source = $map[self::SOURCE] ?? null;
        $name = $map[self::NAME] ?? null;

        return new self(is_string($source) ? $source : '', is_string($name) ? trim($name) : '');
    }

    /**
     * A taxonomy or an attribute is named; the other sources are not.
     *
     * @param list<string> $fixedSources
     * @return array<string, mixed>
     */
    public static function schema(array $fixedSources): array
    {
        return [
            'type' => 'object',
            'anyOf' => [
                [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => [self::SOURCE],
                    'properties' => [
                        self::SOURCE => ['type' => 'string', 'enum' => $fixedSources],
                    ],
                ],
                [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => [self::SOURCE, self::NAME],
                    'properties' => [
                        self::SOURCE => [
                            'type' => 'string',
                            'enum' => [ProductDataSources::TAXONOMY, ProductDataSources::ATTRIBUTE],
                        ],
                        self::NAME => ['type' => 'string', 'pattern' => '\\S'],
                    ],
                ],
            ],
        ];
    }

    public function source(): string
    {
        return $this->source;
    }

    public function name(): string
    {
        return $this->name;
    }
}
