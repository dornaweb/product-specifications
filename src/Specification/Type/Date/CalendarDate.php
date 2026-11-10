<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\Date;

use DateTimeZone;

final class CalendarDate
{
    private const ISO_PATTERN = '/^(\d{4})(?:-(\d{2})(?:-(\d{2}))?)?$/';

    private function __construct(
        private readonly int $year,
        private readonly ?int $month,
        private readonly ?int $day
    ) {
    }

    public static function fromIso(mixed $input): ?self
    {
        $text = is_int($input) ? (string) $input : $input;

        if (!is_string($text) || preg_match(self::ISO_PATTERN, trim($text), $matches) !== 1) {
            return null;
        }

        $year = (int) $matches[1];
        $month = isset($matches[2]) ? (int) $matches[2] : null;
        $day = isset($matches[3]) ? (int) $matches[3] : null;

        return checkdate($month ?? 1, $day ?? 1, $year) ? new self($year, $month, $day) : null;
    }

    public function precision(): DatePrecision
    {
        return match (true) {
            $this->day !== null => DatePrecision::Day,
            $this->month !== null => DatePrecision::Month,
            default => DatePrecision::Year,
        };
    }

    public function covers(DatePrecision $precision): bool
    {
        return $this->precision()->parts() >= $precision->parts();
    }

    /**
     * A date less precise than asked is returned as it is.
     */
    public function cutTo(DatePrecision $precision): self
    {
        if ($this->precision()->parts() <= $precision->parts()) {
            return $this;
        }

        return new self($this->year, $precision === DatePrecision::Year ? null : $this->month, null);
    }

    public function iso(): string
    {
        return sprintf('%04d', $this->year)
            . ($this->month === null ? '' : sprintf('-%02d', $this->month))
            . ($this->day === null ? '' : sprintf('-%02d', $this->day));
    }

    /**
     * The date has no time of day, so it is formatted at midnight UTC and never shifts
     * by the site's time zone.
     */
    public function formatted(): string
    {
        $timestamp = gmmktime(0, 0, 0, $this->month ?? 1, $this->day ?? 1, $this->year);

        if ($timestamp === false) {
            return '';
        }

        $formatted = wp_date($this->precision()->format(), $timestamp, new DateTimeZone('UTC'));

        return is_string($formatted) ? $formatted : '';
    }
}
