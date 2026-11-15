<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Rest;

use Amiut\ProductSpecs\Migration\MigrationState;
use Amiut\ProductSpecs\Specification\SlugAvailability;
use Amiut\ProductSpecs\Specification\SpecificationSlug;
use Amiut\ProductSpecs\Specification\SpecificationTaxonomy;
use stdClass;
use WP_Error;
use WP_REST_Request;
use WP_Term;

/**
 * The slug is the identity every stored value, set structure and export is keyed by.
 * It is chosen once, refused when another specification holds it, and never changed
 * afterward. WordPress would rename a colliding slug silently and let an update change
 * it, so the rule is enforced on the hooks every path passes through.
 */
final class SlugGuard
{
    public const INVALID = 'product_specifications_slug_invalid';
    public const TAKEN = 'product_specifications_slug_taken';

    public function __construct(
        private readonly SlugAvailability $availability,
        private readonly MigrationState $migration
    ) {
    }

    public function register(): void
    {
        add_filter('rest_pre_insert_' . SpecificationTaxonomy::KEY, [$this, 'choose'], 10, 2);
        add_filter('pre_insert_term', [$this, 'refuse'], 10, 3);
        add_filter('wp_update_term_data', [$this, 'freeze'], 10, 3);
    }

    /**
     * On a create, the slug WordPress will store: the client's, sanitized, or one derived
     * from the name and made free. On an update the slug is removed, so it never changes.
     * A client slug that cannot be stored is left as sent, so refuse() names the reason.
     */
    public function choose(stdClass $term, WP_REST_Request $request): stdClass
    {
        if (is_numeric($request->get_param('id'))) {
            unset($term->slug);

            return $term;
        }

        $requested = isset($term->slug) && is_string($term->slug) ? trim($term->slug) : '';

        if ($requested !== '') {
            $term->slug = SpecificationSlug::fromInput($requested)?->value() ?? $requested;

            return $term;
        }

        $name = isset($term->name) && is_string($term->name) ? $term->name : '';
        $term->slug = $this->availability->free(SpecificationSlug::fromName($name))->value();

        return $term;
    }

    /**
     * @param array<string, mixed> $args
     */
    public function refuse(string|WP_Error $term, string $taxonomy, array $args): string|WP_Error
    {
        if ($taxonomy !== SpecificationTaxonomy::KEY || $term instanceof WP_Error) {
            return $term;
        }

        $requested = $args['slug'] ?? '';

        if (!is_string($requested) || $requested === '') {
            return $term;
        }

        $slug = SpecificationSlug::fromInput($requested);

        if ($slug === null) {
            return new WP_Error(
                self::INVALID,
                __(
                    'Enter a key made of letters, digits, dashes and underscores that is not a whole number.',
                    'product-specifications'
                ),
                ['status' => 400]
            );
        }

        if (!$this->availability->taken($slug->value())) {
            return $term;
        }

        return new WP_Error(
            self::TAKEN,
            sprintf(
                /* translators: %s: a specification key. */
                __('"%s" is already the key of another specification.', 'product-specifications'),
                $slug->value()
            ),
            ['status' => 409, 'suggestion' => $this->availability->nextFree($slug)->value()]
        );
    }

    /**
     * Until the site is migrated no product holds a value under a slug, and the migration
     * itself rewrites slugs.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function freeze(array $data, int $termId, string $taxonomy): array
    {
        if ($taxonomy !== SpecificationTaxonomy::KEY || $this->migration->requiresMigration()) {
            return $data;
        }

        $current = get_term($termId, SpecificationTaxonomy::KEY);

        if ($current instanceof WP_Term) {
            $data['slug'] = $current->slug;
        }

        return $data;
    }
}
