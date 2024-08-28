<?php

declare(strict_types=1);

use Amiut\ProductSpecs\Attribute\AttributeFieldGroupCollection;
use Amiut\ProductSpecs\Template\TemplateRenderer;

defined('ABSPATH') || exit;

/**
 * @var AttributeFieldGroupCollection $groupedCollection
 * @var TemplateRenderer $renderer
 * @var array $data
 */

[
    'groupedCollection' => $groupedCollection,
    'renderer' => $renderer,
] = $data;

?>

<ul class="tabs">
    <?php foreach ($groupedCollection as $index => $group) : ?>
        <li
            class="tab <?= $index === 0 ? 'active' : '' ?>"
            data-target="#dwps_attrs_<?= esc_attr((string) $group->id()) ?>"
        >
            <?= esc_html($group->name()) ?>
        </li>
    <?php endforeach ?>
</ul>

<div class="tab-contents">
    <?php foreach ($groupedCollection as $index => $group) : ?>
        <div class="tab-content" id="dwps_attrs_<?= esc_attr((string) $group->id()) ?>">
            <?php if (count($group->attributes())) : ?>
                <ul class="attributes-list">
                    <?= $renderer->render( // phpcs:ignore
                        'admin/metabox/product-specs-table/group-attributes',
                        [
                            'group' => $group, // phpcs:ignore
                        ]
                    ) ?>
                </ul>
            <?php else : ?>
                <?= esc_html__('No attributes defined yet', 'product-specifications') ?>
            <?php endif ?>
        </div>
    <?php endforeach ?>
</div>
