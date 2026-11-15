<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\Link;

final class LinkAddress
{
    public const PROTOCOLS = ['https', 'http', 'mailto', 'tel'];

    private const WEB = ['https', 'http'];

    private function __construct(private readonly string $url, private readonly string $scheme)
    {
    }

    /**
     * Null when the input is empty or is not a URL the type accepts.
     */
    public static function parse(string $input): ?self
    {
        $candidate = trim($input);

        if (!preg_match('/^([a-z][a-z0-9+\-]*):(.*)$/is', $candidate, $parts)) {
            return null;
        }

        $scheme = strtolower($parts[1]);

        if (!in_array($scheme, self::PROTOCOLS, true) || !self::hasTarget($scheme, $candidate, $parts[2])) {
            return null;
        }

        $url = esc_url_raw($candidate, self::PROTOCOLS);

        return $url === '' ? null : new self($url, $scheme);
    }

    public function url(): string
    {
        return $this->url;
    }

    /**
     * The host, or for a URL without one, such as an email address or a phone number,
     * what follows the scheme.
     */
    public function shortForm(): string
    {
        $host = wp_parse_url($this->url, PHP_URL_HOST);

        if (is_string($host) && $host !== '') {
            return $host;
        }

        $target = substr($this->url, strlen($this->scheme) + 1);

        return rawurldecode(explode('?', $target, 2)[0]);
    }

    private static function hasTarget(string $scheme, string $url, string $afterScheme): bool
    {
        if (!in_array($scheme, self::WEB, true)) {
            return trim($afterScheme) !== '';
        }

        $host = wp_parse_url($url, PHP_URL_HOST);

        return is_string($host) && $host !== '' && preg_match('/\s/', $host) !== 1;
    }
}
