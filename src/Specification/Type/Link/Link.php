<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\Link;

final class Link
{
    private const URL = 'url';
    private const LABEL = 'label';

    private function __construct(private readonly LinkAddress $address, private readonly string $label)
    {
    }

    public static function isEmpty(mixed $data): bool
    {
        if (!is_array($data)) {
            return self::isBlank($data);
        }

        return self::isBlank($data[self::URL] ?? null) && self::isBlank($data[self::LABEL] ?? null);
    }

    /**
     * Reads `{url, label}`, or a bare address without a label. Null when there is no
     * address the type accepts.
     */
    public static function fromData(mixed $data): ?self
    {
        if (is_string($data)) {
            return self::create($data, '');
        }

        if (!is_array($data) || !is_string($data[self::URL] ?? null)) {
            return null;
        }

        $label = $data[self::LABEL] ?? '';

        return self::create($data[self::URL], is_string($label) ? $label : '');
    }

    /**
     * A link without a label is stored without the key.
     *
     * @return array{url: string, label?: string}
     */
    public function toStorage(): array
    {
        $url = [self::URL => $this->address->url()];

        return $this->label === '' ? $url : [...$url, self::LABEL => $this->label];
    }

    public function plainText(): string
    {
        return $this->label === '' ? $this->address->url() : "{$this->label}: {$this->address->url()}";
    }

    public function html(): string
    {
        $text = $this->label === '' ? $this->address->shortForm() : $this->label;

        return sprintf(
            '<a href="%s" target="_blank" rel="nofollow noopener">%s</a>',
            esc_url($this->address->url(), LinkAddress::PROTOCOLS),
            esc_html($text)
        );
    }

    private static function isBlank(mixed $part): bool
    {
        return $part === null || (is_string($part) && trim($part) === '');
    }

    private static function create(string $url, string $label): ?self
    {
        $address = LinkAddress::parse($url);

        return $address === null ? null : new self($address, sanitize_text_field($label));
    }
}
