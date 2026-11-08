<?php

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

defined('OBJECT') || define('OBJECT', 'OBJECT');

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

if (!function_exists('delete_option')) {
    function delete_option(string $option): bool
    {
        unset($GLOBALS['wp_test_options'][$option]);

        return true;
    }
}

if (!function_exists('wp_upload_dir')) {
    /**
     * @return array<string, string>
     */
    function wp_upload_dir(?string $time = null, bool $createDirectory = true): array
    {
        return ['basedir' => $GLOBALS['wp_test_upload_dir'] ?? sys_get_temp_dir()];
    }
}

if (!function_exists('wp_mkdir_p')) {
    function wp_mkdir_p(string $target): bool
    {
        // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.directory_mkdir -- No WordPress in unit tests.
        return is_dir($target) || mkdir($target, 0777, true);
    }
}

if (!function_exists('wp_generate_password')) {
    function wp_generate_password(int $length = 12, bool $specialChars = true): string
    {
        return bin2hex(random_bytes(intdiv($length, 2)));
    }
}

if (!function_exists('wp_delete_file')) {
    function wp_delete_file(string $file): void
    {
        unlink($file);
    }
}

$GLOBALS['wp_test_posts'] = [];
$GLOBALS['wp_test_post_meta'] = [];
$GLOBALS['wp_test_post_types'] = [];
$GLOBALS['wp_test_terms'] = [];
$GLOBALS['wp_test_actions'] = [];

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

if (!function_exists('get_post_meta')) {
    function get_post_meta(int $postId, string $key = '', bool $single = false): mixed
    {
        $value = $GLOBALS['wp_test_post_meta'][$postId][$key] ?? '';

        return $single ? $value : [$value];
    }
}

if (!function_exists('update_post_meta')) {
    function update_post_meta(int $postId, string $key, mixed $value): int|bool
    {
        $current = $GLOBALS['wp_test_post_meta'][$postId][$key] ?? null;
        if ($current === $value) {
            return false;
        }

        $GLOBALS['wp_test_post_meta'][$postId][$key] = $value;

        return 1;
    }
}

if (!function_exists('delete_post_meta')) {
    function delete_post_meta(int $postId, string $key): bool
    {
        unset($GLOBALS['wp_test_post_meta'][$postId][$key]);

        return true;
    }
}

if (!function_exists('metadata_exists')) {
    function metadata_exists(string $type, int $postId, string $key): bool
    {
        return array_key_exists($key, $GLOBALS['wp_test_post_meta'][$postId] ?? []);
    }
}

if (!function_exists('get_post_type')) {
    function get_post_type(int $postId): string|false
    {
        return $GLOBALS['wp_test_post_types'][$postId] ?? false;
    }
}

if (!function_exists('do_action')) {
    function do_action(string $hookName, mixed ...$args): void
    {
        $GLOBALS['wp_test_actions'][] = [$hookName, $args];
    }
}

if (!function_exists('register_taxonomy')) {
    /**
     * @param array<string, mixed> $args
     */
    function register_taxonomy(string $taxonomy, string $objectType, array $args = []): void
    {
        $GLOBALS['wp_test_taxonomies'][$taxonomy] = $args;
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

if (!class_exists('WP_Post')) {
    // phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
    // phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ValidClassName.NotCamelCaps -- WordPress owns this name.
    class WP_Post
    {
        public int $ID = 0;

        public string $post_type = '';

        public string $post_name = '';

        public string $post_title = '';

        public string $post_status = 'publish';

        public string $post_content = '';
    }
    // phpcs:enable PSR1.Classes.ClassDeclaration.MultipleClasses
}

if (!function_exists('get_term')) {
    function get_term(int $termId, string $taxonomy = ''): mixed
    {
        return $GLOBALS['wp_test_terms'][$termId] ?? false;
    }
}

if (!function_exists('get_term_by')) {
    function get_term_by(string $field, string|int $value, string $taxonomy = ''): mixed
    {
        foreach ($GLOBALS['wp_test_terms'] as $term) {
            if ($term instanceof WP_Term && $field === 'slug' && $term->slug === $value) {
                return $term;
            }
        }

        return false;
    }
}

if (!function_exists('get_page_by_path')) {
    function get_page_by_path(string $slug, string $output = OBJECT, string $postType = 'page'): mixed
    {
        foreach ($GLOBALS['wp_test_posts_by_id'] ?? [] as $post) {
            if ($post instanceof WP_Post && $post->post_name === $slug && $post->post_type === $postType) {
                return $post;
            }
        }

        return null;
    }
}

if (!class_exists('WP_REST_Request')) {
    // phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses, PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    // phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ValidClassName.NotCamelCaps -- WordPress owns this name.
    class WP_REST_Request
    {
        /**
         * @param array<string, mixed> $params
         */
        public function __construct(private readonly array $params = [])
        {
        }

        public function get_param(string $key): mixed
        {
            return $this->params[$key] ?? null;
        }
    }
    // phpcs:enable PSR1.Classes.ClassDeclaration.MultipleClasses, PSR1.Methods.CamelCapsMethodName.NotCamelCaps
}

if (!function_exists('sanitize_title')) {
    function sanitize_title(string $title): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $title) ?? ''));

        return trim($slug, '-');
    }
}

$GLOBALS['wp_test_scheduled'] = [];

if (!function_exists('wp_next_scheduled')) {
    function wp_next_scheduled(string $hook, array $args = []): int|false
    {
        return $GLOBALS['wp_test_scheduled'][$hook] ?? false;
    }
}

if (!function_exists('wp_schedule_single_event')) {
    function wp_schedule_single_event(int $timestamp, string $hook, array $args = [], bool $wpError = false): bool
    {
        $GLOBALS['wp_test_scheduled'][$hook] = $timestamp;

        return true;
    }
}

if (!function_exists('get_post_stati')) {
    /**
     * @return array<string, string>
     */
    function get_post_stati(): array
    {
        return ['publish' => 'publish', 'trash' => 'trash'];
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES);
    }
}

if (!class_exists('WC_Product')) {
    // phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses, PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    // phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ValidClassName.NotCamelCaps -- WooCommerce owns this name.
    class WC_Product
    {
        public function __construct(private readonly int $id = 0)
        {
        }

        public function get_id(): int
        {
            return $this->id;
        }
    }
    // phpcs:enable PSR1.Classes.ClassDeclaration.MultipleClasses, PSR1.Methods.CamelCapsMethodName.NotCamelCaps
}

if (!function_exists('_n')) {
    function _n(string $single, string $plural, int $number, string $domain = 'default'): string
    {
        return $number === 1 ? $single : $plural;
    }
}

if (!function_exists('rest_validate_value_from_schema')) {
    /**
     * @param array<string, mixed> $args
     */
    function rest_validate_value_from_schema(mixed $value, array $args, string $param = ''): bool
    {
        return true;
    }
}

if (!function_exists('rest_sanitize_value_from_schema')) {
    /**
     * @param array<string, mixed> $args
     */
    function rest_sanitize_value_from_schema(mixed $value, array $args, string $param = ''): mixed
    {
        return $value;
    }
}

$GLOBALS['wp_test_skus'] = [];

if (!function_exists('wc_get_product_id_by_sku')) {
    function wc_get_product_id_by_sku(string $sku): int
    {
        return $GLOBALS['wp_test_skus'][$sku] ?? 0;
    }
}

if (!function_exists('wp_json_encode')) {
    function wp_json_encode(mixed $value, int $flags = 0, int $depth = 512): string|false
    {
        return json_encode($value, $flags, $depth);
    }
}

if (!function_exists('home_url')) {
    function home_url(string $path = ''): string
    {
        return 'https://store.test' . $path;
    }
}
