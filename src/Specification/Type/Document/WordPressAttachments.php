<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\Document;

use WP_Post;

final class WordPressAttachments implements Attachments
{
    private const FILE_META_KEY = '_wp_attached_file';

    public function find(int $id): ?Attachment
    {
        $post = get_post($id);

        if (!$post instanceof WP_Post || $post->post_type !== 'attachment') {
            return null;
        }

        $url = wp_get_attachment_url($id);

        if (!is_string($url) || $url === '') {
            return null;
        }

        $file = get_post_meta($id, self::FILE_META_KEY, true);
        $metadata = wp_get_attachment_metadata($id);
        $size = is_array($metadata) && is_int($metadata['filesize'] ?? null) ? $metadata['filesize'] : null;

        return new Attachment(
            $id,
            html_entity_decode(get_the_title($post), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            $url,
            is_string($file) ? $file : '',
            $size
        );
    }

    public function idForFile(string $file): ?int
    {
        // phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key
        // phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_value
        $ids = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'meta_key' => self::FILE_META_KEY,
            'meta_value' => $file,
            'fields' => 'ids',
            'posts_per_page' => 1,
            'no_found_rows' => true,
        ]);
        // phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_value
        // phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_key

        $id = $ids[0] ?? null;

        return is_int($id) ? $id : null;
    }
}
