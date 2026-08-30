<?php

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

/**
 * Stand-ins for the WordPress functions the unit suite reaches. An integration
 * harness will replace this file; until then a missing shim must fail loudly
 * rather than be guessed at, so only add what a test actually calls.
 */

if (!function_exists('esc_html')) {
    function esc_html(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES);
    }
}

if (!function_exists('__')) {
    function __(string $text, string $domain = 'default'): string
    {
        return $text;
    }
}

$GLOBALS['wp_test_options'] = [];

if (!function_exists('get_option')) {
    function get_option(string $option, mixed $default = false): mixed
    {
        return $GLOBALS['wp_test_options'][$option] ?? $default;
    }
}

if (!function_exists('update_option')) {
    function update_option(string $option, mixed $value, bool $autoload = true): bool
    {
        $GLOBALS['wp_test_options'][$option] = $value;

        return true;
    }
}

$GLOBALS['wp_test_posts'] = [];

if (!function_exists('get_posts')) {
    /**
     * @param array<string, mixed> $args
     * @return array<int>
     */
    function get_posts(array $args = []): array
    {
        return $GLOBALS['wp_test_posts'];
    }
}

if (!function_exists('sanitize_key')) {
    function sanitize_key(string $key): string
    {
        return (string) preg_replace('/[^a-z0-9_\-]/', '', strtolower($key));
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field(string $text): string
    {
        $stripped = (string) preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $text);
        $stripped = (string) preg_replace('/<[^>]*>/', '', $stripped);

        return trim((string) preg_replace('/[\r\n\t ]+/', ' ', $stripped));
    }
}

if (!function_exists('absint')) {
    function absint(mixed $value): int
    {
        return abs((int) $value);
    }
}

if (!function_exists('esc_html__')) {
    function esc_html__(string $text, string $domain = 'default'): string
    {
        return htmlspecialchars($text, ENT_QUOTES);
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters(string $hookName, mixed $value): mixed
    {
        return $value;
    }
}

if (!function_exists('_x')) {
    function _x(string $text, string $context, string $domain = 'default'): string
    {
        return $text;
    }
}

$GLOBALS['wp_test_term_meta'] = [];

if (!function_exists('get_term_meta')) {
    function get_term_meta(int $termId, string $key = '', bool $single = false): mixed
    {
        $value = $GLOBALS['wp_test_term_meta'][$termId][$key] ?? '';

        return $single ? $value : [$value];
    }
}

if (!class_exists('WP_Term')) {
    // phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ValidClassName.NotCamelCaps -- WordPress owns this name.
    class WP_Term
    {
        public int $term_id = 0;

        public string $name = '';

        public string $slug = '';

        public string $description = '';
    }
}
