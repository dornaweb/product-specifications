<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\WooCommerce;

abstract class TextSource implements ProductDataSource
{
    final public function plainValue(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }

    final public function render(mixed $value): string
    {
        return esc_html($this->plainValue($value));
    }

    /**
     * WooCommerce's formatters and stored term names carry HTML entities, such as the
     * `&times;` between dimensions.
     */
    final protected static function text(string $html): ?string
    {
        $text = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return $text === '' ? null : $text;
    }
}
