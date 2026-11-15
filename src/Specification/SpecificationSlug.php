<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification;

final class SpecificationSlug
{
    private const COLUMN_LENGTH = 200;
    private const UNNAMED = 'specification';

    private function __construct(private readonly string $value)
    {
    }

    /**
     * Null when nothing usable remains after sanitizing, or when the result is a canonical
     * integer string, which PHP would cast to an integer array key.
     */
    public static function fromInput(string $input): ?self
    {
        $slug = self::fitted(sanitize_title($input), self::COLUMN_LENGTH);

        if ($slug === '' || self::isCanonicalIntegerString($slug)) {
            return null;
        }

        return new self($slug);
    }

    /**
     * The key a name derives. It may still be a canonical integer string or taken.
     */
    public static function fromName(string $name): self
    {
        $slug = self::fitted(sanitize_title($name), self::COLUMN_LENGTH);

        return new self($slug === '' ? self::UNNAMED : $slug);
    }

    public static function preserved(string $slug): self
    {
        return new self($slug);
    }

    public static function isCanonicalIntegerString(string $value): bool
    {
        return (string) (int) $value === $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isCanonicalInteger(): bool
    {
        return self::isCanonicalIntegerString($this->value);
    }

    public function withNumericSuffix(int $number): self
    {
        $suffix = '-' . $number;

        return new self(self::fitted($this->value, self::COLUMN_LENGTH - strlen($suffix)) . $suffix);
    }

    // Non-Latin characters are stored percent-encoded, one triplet per byte, so a cut
    // backs off by whole triplets until what remains decodes to complete characters.
    private static function fitted(string $slug, int $limit): string
    {
        if (strlen($slug) > $limit) {
            $slug = substr($slug, 0, $limit);
        }

        while ($slug !== '' && !self::decodesCleanly($slug)) {
            $percent = strrpos($slug, '%');
            $slug = $percent === false ? '' : substr($slug, 0, $percent);
        }

        return rtrim($slug, '-');
    }

    private static function decodesCleanly(string $slug): bool
    {
        return preg_match('/%[0-9a-fA-F]?$/', $slug) !== 1
            && mb_check_encoding(rawurldecode($slug), 'UTF-8');
    }
}
