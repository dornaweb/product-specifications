<?php

declare(strict_types=1);

/**
 * @var WP_Post $post
 * @var array<WP_Term> $groups
 * @var array<WP_Term> $selectedGroups
 * @var array $data
 */
[
    'post' => $post,
    'groups' => $groups,
    'selectedGroups' => $selectedGroups,
] = $data;

?>

<div class="dwsp-meta-wrap">
    <strong class="title"><?= esc_html__('Attribute Groups : ', 'product-specifications') ?></strong>
    <span class="hint">
        <?= esc_html__(
            'Select attribute groups you want to load in this table : ',
            'product-specifications'
        ) ?>
    </span>

    <div class="dwsp-meta-item">
        <?php if (count($groups) > 0) : ?>
            <?php foreach ($groups as $term) :
                $slug = dwspecs_spec_group_has_duplicates($term->name)
                    ? " ({$term->slug})"
                    : "";
                $isChecked = count(
                    array_filter(
                        $selectedGroups,
                        static fn (WP_Term $selectedGroup) => $selectedGroup->term_id === $term->term_id
                    )
                ) > 0 ?>
                <p>
                    <label>
                        <input
                            type="checkbox"
                            value="<?= esc_attr((string) $term->term_id); ?>"
                            <?php checked($isChecked) ?>
                        >
                        <span><?= esc_html($term->name) ?><?= esc_html($slug) ?></span>
                    </label>
                </p>
            <?php endforeach ?>
        <?php else : ?>
            <?= esc_html__(
                'No Group found, Please define some groups first',
                'product-specifications'
            ) ?>
        <?php endif ?>

        <ul class="table-groups-list dpws-sortable">
            <?php foreach ($selectedGroups as $group) :
                $slug = dwspecs_spec_group_has_duplicates($group->name)
                    ? " ({$group->slug})"
                    : "" ?>
                    <li>
                        <input
                            checked
                            type="checkbox"
                            name="groups[]"
                            value="<?= esc_attr((string) $group->term_id) ?>"
                            readonly
                        >
                        <?= esc_html($group->name) ?>
                        <?= esc_html($slug) ?>
                    </li>
            <?php endforeach ?>
        </ul>
    </div>
</div>
