<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Slug;

use WP_Post;
use WP_Term;

/**
 * A stored slug can hold characters sanitize_title() strips, such as the zero-width
 * non-joiner common in Persian. get_page_by_path() and get_term_by() sanitize before they
 * look up, so a slug like that is looked up as stored when they find nothing.
 */
final class SlugLookup
{
    public static function post(string $slug, string $postType): ?WP_Post
    {
        $post = get_page_by_path($slug, 'OBJECT', $postType);

        if ($post instanceof WP_Post || sanitize_title($slug) === $slug) {
            return $post instanceof WP_Post ? $post : null;
        }

        /** @var \wpdb $wpdb */
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- No WordPress API looks a post up by its slug unsanitized.
        $postId = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT ID FROM %i WHERE post_type = %s AND post_name = %s LIMIT 1',
                $wpdb->posts,
                $postType,
                $slug
            )
        );
        $post = is_numeric($postId) ? get_post((int) $postId) : null;

        return $post instanceof WP_Post ? $post : null;
    }

    public static function term(string $slug, string $taxonomy): ?WP_Term
    {
        $term = get_term_by('slug', $slug, $taxonomy);

        if ($term instanceof WP_Term || sanitize_title($slug) === $slug) {
            return $term instanceof WP_Term ? $term : null;
        }

        /** @var \wpdb $wpdb */
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- No WordPress API looks a term up by its slug unsanitized.
        $termId = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT t.term_id FROM %i AS t INNER JOIN %i AS tt ON tt.term_id = t.term_id'
                . ' WHERE tt.taxonomy = %s AND t.slug = %s LIMIT 1',
                $wpdb->terms,
                $wpdb->term_taxonomy,
                $taxonomy,
                $slug
            )
        );
        $term = is_numeric($termId) ? get_term((int) $termId, $taxonomy) : null;

        return $term instanceof WP_Term ? $term : null;
    }
}
