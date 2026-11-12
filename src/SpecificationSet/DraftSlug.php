<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet;

/**
 * WordPress leaves a draft without a slug until it is published. Products and import files
 * point at a set by its slug, so a draft set is given one too.
 */
final class DraftSlug
{
    private const UNNAMED = 'specification-set';

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $postarr
     * @return array<string, mixed>
     */
    public function assign(array $data, array $postarr): array
    {
        if (
            ($data['post_type'] ?? null) !== SpecificationSetPostType::KEY
            || ($data['post_name'] ?? '') !== ''
            || !in_array($data['post_status'] ?? null, ['draft', 'pending'], true)
        ) {
            return $data;
        }

        $title = is_string($data['post_title'] ?? null) ? wp_unslash($data['post_title']) : '';
        $slug = sanitize_title(is_string($title) ? $title : '');

        // WordPress keeps only published slugs unique, so the slug is checked as if published.
        $data['post_name'] = wp_unique_post_slug(
            $slug === '' ? self::UNNAMED : $slug,
            is_numeric($postarr['ID'] ?? null) ? (int) $postarr['ID'] : 0,
            'publish',
            SpecificationSetPostType::KEY,
            0
        );

        return $data;
    }
}
