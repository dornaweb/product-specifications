<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type;

use Amiut\ProductSpecs\Specification\SpecificationOptions;

final class TextOptions
{
    public const SUGGEST_KEY = 'suggest';
    public const SEVERAL_KEY = 'multiple';
    public const LEARN_KEY = 'learn';

    private function __construct(private readonly SpecificationOptions $options)
    {
    }

    public static function of(SpecificationOptions $options): self
    {
        return new self($options);
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
                self::SUGGEST_KEY => ['type' => 'boolean'],
                SpecificationOptions::CHOICES_KEY => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                self::SEVERAL_KEY => ['type' => 'boolean'],
                self::LEARN_KEY => ['type' => 'boolean'],
            ],
        ];
    }

    public function suggests(): bool
    {
        return $this->flag(self::SUGGEST_KEY);
    }

    public function allowsSeveral(): bool
    {
        return $this->flag(self::SEVERAL_KEY);
    }

    public function learns(): bool
    {
        return $this->suggests() && $this->flag(self::LEARN_KEY);
    }

    /**
     * @return list<string>
     */
    public function suggestions(): array
    {
        return $this->options->choices();
    }

    private function flag(string $key): bool
    {
        return ($this->options->toArray()[$key] ?? false) === true;
    }
}
