<?php

declare(strict_types=1);

use Amiut\ProductSpecs\Attribute\AttributeFieldGroupCollection;
use Amiut\ProductSpecs\Template\TemplateRenderer;

defined('ABSPATH') || exit;

/**
 * @var int $currentTableId
 * @var array<WP_Post> $tables
 * @var WP_Post $post
 * @var AttributeFieldGroupCollection $groupedCollection
 * @var TemplateRenderer $renderer
 * @var array $data
 */

[
    'currentTableId' => $currentTableId,
    'tables' => $tables,
    'renderer' => $renderer,
    'post' => $post,
    'groupedCollection' => $groupedCollection,
] = $data;

?>

<div class="dwsp-meta-wrap dwps-page">
    <strong class="title">
        <?= esc_html__('Select a table : ', 'product-specifications') ?>
    </strong>

    <div class="dwsp-meta-item">
        <select
            name="specs_table"
            id="spec_tables_list"
            data-postid="<?= esc_attr((string) $post->ID) ?>"
        >
            <option value="0">
                <?= esc_html__('none', 'product-specifications') ?>
            </option>

            <?php foreach ($tables as $table) : ?>
                <option
                    value="<?= esc_attr((string) $table->ID) ?>"
                    <?php selected($currentTableId, $table->ID) ?>
                >
                    <?= esc_html($table->post_title) ?>
                    <?= esc_html(
                        sprintf(
                            // translators: %d is table id
                            __(
                                '(ID: %d)',
                                'product-specifications'
                            ),
                            $table->ID,
                        )
                    ) ?>
                </option>
            <?php endforeach ?>
        </select>
    </div>

    <div
        class="dwps-spec-table-wrap tab-boxes clearfix"
        id="specifications_table_wrapper"
    >
        <?php // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <?= $renderer->render(
            'admin/metabox/product-specs-table/product-attribute-fields',
            [
                'groupedCollection' => $groupedCollection,
            ]
        ) ?>
        <?php // phpcs:enable ?>
    </div>
</div>

<script id="loading_svg" type="x-tmpl-mustache">
    <svg
        width="120" height="30" viewBox="0 0 120 30" xmlns="http://www.w3.org/2000/svg" fill="#fff">
        <circle cx="15" cy="15" r="15">
            <animate attributeName="r" from="15" to="15"
                    begin="0s" dur="0.8s"
                    values="15;9;15" calcMode="linear"
                    repeatCount="indefinite" />
            <animate attributeName="fill-opacity" from="1" to="1"
                    begin="0s" dur="0.8s"
                    values="1;.5;1" calcMode="linear"
                    repeatCount="indefinite" />
        </circle>
        <circle cx="60" cy="15" r="9" fill-opacity="0.3">
            <animate attributeName="r" from="9" to="9"
                    begin="0s" dur="0.8s"
                    values="9;15;9" calcMode="linear"
                    repeatCount="indefinite" />
            <animate attributeName="fill-opacity" from="0.5" to="0.5"
                    begin="0s" dur="0.8s"
                    values=".5;1;.5" calcMode="linear"
                    repeatCount="indefinite" />
        </circle>
        <circle cx="105" cy="15" r="15">
            <animate attributeName="r" from="15" to="15"
                    begin="0s" dur="0.8s"
                    values="15;9;15" calcMode="linear"
                    repeatCount="indefinite" />
            <animate attributeName="fill-opacity" from="1" to="1"
                    begin="0s" dur="0.8s"
                    values="1;.5;1" calcMode="linear"
                    repeatCount="indefinite" />
        </circle>
    </svg>
</script>
