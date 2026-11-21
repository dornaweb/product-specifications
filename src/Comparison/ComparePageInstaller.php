<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Comparison;

use Amiut\ProductSpecs\Block\Comparison\ComparisonBlock;
use Amiut\ProductSpecs\Settings\Settings;
use Amiut\ProductSpecs\Settings\SettingsRepository;
use WP_Error;

final class ComparePageInstaller
{
    public const OPTION = 'product_specifications_compare_page_created';

    public function __construct(private readonly SettingsRepository $settings)
    {
    }

    public function install(): void
    {
        if (get_option(self::OPTION) !== false) {
            return;
        }

        // A second request racing this one inserts the same row, which MySQL reports as no
        // change, so add_option() answers false for it. A varying value would let both pass.
        if (!add_option(self::OPTION, 'yes', '', true)) {
            return;
        }

        if ($this->settings->current()->comparePageId() > 0) {
            return;
        }

        $pageId = wp_insert_post(
            [
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_title' => __('Compare', 'product-specifications'),
                'post_content' => sprintf('<!-- wp:%s {"align":"wide"} /-->', ComparisonBlock::NAME),
            ],
            true
        );

        if (!$pageId instanceof WP_Error && $pageId > 0) {
            $this->settings->update(Settings::COMPARE_PAGE, $pageId);
        }
    }
}
