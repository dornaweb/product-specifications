<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Comparison;

use Amiut\ProductSpecs\Settings\SettingsRepository;

final class CompareRobots
{
    public const NOINDEX_FILTER = 'product_specifications_comparison_noindex';

    public function __construct(private readonly SettingsRepository $settings)
    {
    }

    /**
     * @wp-hook wp_robots
     *
     * @param array<string, bool|string> $robots
     * @return array<string, bool|string>
     */
    public function addNoindex(array $robots): array
    {
        $pageId = $this->noindexedPageId();

        return $pageId > 0 && is_page($pageId) ? wp_robots_no_robots($robots) : $robots;
    }

    /**
     * @wp-hook wp_sitemaps_posts_query_args
     *
     * @param array<string, mixed> $args
     * @return array<string, mixed>
     */
    public function excludeFromSitemap(array $args, string $postType): array
    {
        $pageId = $this->noindexedPageId();

        if ($postType !== 'page' || $pageId < 1) {
            return $args;
        }

        $excluded = $args['post__not_in'] ?? [];
        // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- One page id.
        $args['post__not_in'] = [...(is_array($excluded) ? $excluded : []), $pageId];

        return $args;
    }

    private function noindexedPageId(): int
    {
        return apply_filters(self::NOINDEX_FILTER, true) ? $this->settings->current()->comparePageId() : 0;
    }
}
