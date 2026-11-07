<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type;

use Amiut\ProductSpecs\Specification\SpecificationOptions;

final class YesNoDisplay
{
    public const DISPLAY_KEY = 'display';
    public const LABELS_KEY = 'labels';
    public const ICONS = 'icons';
    public const TEXT = 'text';
    public const LABEL_SEPARATOR = '|';

    private const YES_ICON = 'M29.839 10.107q0 0.714-0.5 1.214l-15.357 15.357q-0.5 0.5-1.214 0.5t-1.214-0.5'
        . 'l-8.893-8.893q-0.5-0.5-0.5-1.214t0.5-1.214l2.429-2.429q0.5-0.5 1.214-0.5t1.214 0.5l5.25 5.268 11.714-11.732'
        . 'q0.5-0.5 1.214-0.5t1.214 0.5l2.429 2.429q0.5 0.5 0.5 1.214z';
    private const NO_ICON = 'M23.179 23.607q0 0.714-0.5 1.214l-2.429 2.429q-0.5 0.5-1.214 0.5t-1.214-0.5l-5.25-5.25'
        . '-5.25 5.25q-0.5 0.5-1.214 0.5t-1.214-0.5l-2.429-2.429q-0.5-0.5-0.5-1.214t0.5-1.214l5.25-5.25-5.25-5.25'
        . 'q-0.5-0.5-0.5-1.214t0.5-1.214l2.429-2.429q0.5-0.5 1.214-0.5t1.214 0.5l5.25 5.25 5.25-5.25'
        . 'q0.5-0.5 1.214-0.5t1.214 0.5l2.429 2.429q0.5 0.5 0.5 1.214t-0.5 1.214l-5.25 5.25 5.25 5.25'
        . 'q0.5 0.5 0.5 1.214z';

    private function __construct(
        private readonly bool $asText,
        private readonly ?string $yesLabel,
        private readonly ?string $noLabel
    ) {
    }

    /**
     * Custom labels are written as "yes|no". Anything short of both parts falls
     * back to the translated words.
     */
    public static function fromOptions(SpecificationOptions $options): self
    {
        $map = $options->toArray();
        $labels = $map[self::LABELS_KEY] ?? null;
        $parts = is_string($labels) ? explode(self::LABEL_SEPARATOR, $labels, 2) : [];
        $yes = trim($parts[0] ?? '');
        $no = trim($parts[1] ?? '');
        $hasBoth = $yes !== '' && $no !== '';

        return new self(
            ($map[self::DISPLAY_KEY] ?? null) === self::TEXT,
            $hasBoth ? $yes : null,
            $hasBoth ? $no : null
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function optionsSchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                self::DISPLAY_KEY => [
                    'type' => 'string',
                    'enum' => [self::ICONS, self::TEXT],
                ],
                self::LABELS_KEY => [
                    'type' => 'string',
                    'maxLength' => 100,
                ],
            ],
        ];
    }

    public function render(bool $value): string
    {
        $label = $value
            ? ($this->yesLabel ?? __('Yes', 'product-specifications'))
            : ($this->noLabel ?? __('No', 'product-specifications'));

        if ($this->asText) {
            return esc_html($label);
        }

        return sprintf(
            '<svg class="ps-yes-no ps-yes-no--%1$s" role="img" aria-label="%2$s" viewBox="%3$s"'
            . ' width="1em" height="1em" fill="currentColor" focusable="false"><path d="%4$s"></path></svg>',
            $value ? 'yes' : 'no',
            esc_attr($label),
            $value ? '0 0 32 32' : '0 0 25 32',
            $value ? self::YES_ICON : self::NO_ICON
        );
    }
}
