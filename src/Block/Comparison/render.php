<?php

declare(strict_types=1);

use Amiut\ProductSpecs\Block\Comparison\ComparisonBlock;
use Amiut\ProductSpecs\Comparison\ProductHeader;

use function Amiut\ProductSpecs\plugin;

defined('ABSPATH') || exit;

/**
 * @var ComparisonBlock $comparisonBlock
 */
$comparisonBlock = plugin()->container()->get(ComparisonBlock::class);
$comparison = $comparisonBlock->comparison();
$products = $comparison->products();

?>
<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress escapes block wrapper attributes. ?>
<div <?= get_block_wrapper_attributes(['class' => 'ps-comparison']) ?>>
    <?php if ($comparison->isEmpty()) : ?>
        <p class="ps-comparison__empty">
            <?= esc_html__('Add products to compare from a product page.', 'product-specifications') ?>
            <a href="<?= esc_url(wc_get_page_permalink('shop')) ?>">
                <?= esc_html__('Go to the shop', 'product-specifications') ?>
            </a>
        </p>
    <?php else : ?>
        <?php if ($comparisonBlock->isFull($comparison)) : ?>
            <p class="ps-comparison__limit">
                <?= esc_html(
                    sprintf(
                        /* translators: %d: the most products one comparison holds. */
                        _n(
                            'You can compare up to %d product.',
                            'You can compare up to %d products.',
                            $comparisonBlock->maximum(),
                            'product-specifications'
                        ),
                        $comparisonBlock->maximum()
                    )
                ) ?>
            </p>
        <?php else : ?>
            <?php
            $searchId = wp_unique_id('ps-comparison-search-');
            wp_interactivity_state(ComparisonBlock::STORE, $comparisonBlock->searchState());
            ?>
            <div
                class="ps-comparison__add"
                data-wp-interactive="<?= esc_attr(ComparisonBlock::STORE) ?>"
                data-wp-class--is-open="context.isOpen"
                <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress escapes the context attribute. ?>
                <?= wp_interactivity_data_wp_context($comparisonBlock->searchContext($comparison)) ?>
            >
                <button
                    type="button"
                    class="ps-comparison__add-button"
                    data-wp-bind--hidden="context.isOpen"
                    data-wp-on--click="actions.open"
                    data-wp-watch="callbacks.focusWhenClosed"
                >
                    <span class="ps-comparison__add-icon" aria-hidden="true">+</span>
                    <?= esc_html__('Add product', 'product-specifications') ?>
                </button>
                <div
                    class="ps-comparison__picker"
                    hidden
                    data-wp-bind--hidden="!context.isOpen"
                    data-wp-on--focusout="actions.leave"
                >
                    <label class="screen-reader-text" for="<?= esc_attr($searchId) ?>">
                        <?= esc_html__('Add a product to compare', 'product-specifications') ?>
                    </label>
                    <input
                        id="<?= esc_attr($searchId) ?>"
                        class="ps-comparison__input"
                        type="search"
                        role="combobox"
                        autocomplete="off"
                        aria-autocomplete="list"
                        aria-expanded="false"
                        aria-controls="<?= esc_attr($searchId . '-results') ?>"
                        placeholder="<?= esc_attr__('Search products', 'product-specifications') ?>"
                        data-wp-bind--value="context.query"
                        data-wp-bind--aria-expanded="state.hasResults"
                        data-wp-bind--aria-activedescendant="state.activeId"
                        data-wp-on--input="actions.search"
                        data-wp-on--keydown="actions.navigate"
                        data-wp-watch="callbacks.focusWhenOpen"
                    />
                    <ul
                        id="<?= esc_attr($searchId . '-results') ?>"
                        class="ps-comparison__results"
                        role="listbox"
                        aria-label="<?= esc_attr__('Matching products', 'product-specifications') ?>"
                        hidden
                        data-wp-bind--hidden="!state.hasResults"
                        data-wp-on--mousedown="actions.keepFocus"
                    >
                        <template
                            data-wp-each--result="context.results"
                            data-wp-each-key="context.result.id"
                        >
                            <li
                                class="ps-comparison__option"
                                role="option"
                                data-wp-bind--id="state.optionId"
                                data-wp-bind--aria-selected="state.isActive"
                                data-wp-class--is-active="state.isActive"
                                data-wp-on--click="actions.pick"
                            >
                                <img
                                    class="ps-comparison__option-image"
                                    alt=""
                                    width="40"
                                    height="40"
                                    data-wp-bind--src="context.result.image"
                                    data-wp-bind--hidden="!context.result.image"
                                />
                                <span data-wp-text="context.result.name"></span>
                            </li>
                        </template>
                    </ul>
                    <p
                        class="ps-comparison__status"
                        aria-live="polite"
                        data-wp-class--screen-reader-text="state.hasResults"
                        data-wp-text="state.statusText"
                    ></p>
                    <button
                        type="button"
                        class="ps-comparison__retry"
                        hidden
                        data-wp-bind--hidden="!state.hasFailed"
                        data-wp-on--click="actions.retry"
                    >
                        <?= esc_html__('Retry', 'product-specifications') ?>
                    </button>
                </div>
            </div>
        <?php endif ?>
        <div class="ps-comparison__scroll">
            <table
                class="ps-comparison__table"
                role="table"
                style="--ps-columns: <?= (int) count($products) ?>"
            >
                <caption class="screen-reader-text">
                    <?= esc_html__('Product comparison', 'product-specifications') ?>
                </caption>
                <thead role="rowgroup">
                    <tr role="row">
                        <td class="ps-comparison__corner" role="cell"></td>
                        <?php foreach ($products as $product) : ?>
                            <th class="ps-comparison__product" scope="col" role="columnheader">
                                <?php
                                $removeLabel = sprintf(
                                    /* translators: %s: product name. */
                                    __('Remove %s from the comparison', 'product-specifications'),
                                    $product->get_name()
                                );
                                ?>
                                <a
                                    class="ps-comparison__remove"
                                    href="<?= esc_url($comparisonBlock->removeUrl($comparison, $product)) ?>"
                                    aria-label="<?= esc_attr($removeLabel) ?>"
                                >&times;</a>
                                <?php do_action(ProductHeader::ACTION, $product) ?>
                            </th>
                        <?php endforeach ?>
                    </tr>
                </thead>
                <?php foreach ($comparison->groups() as $group) : ?>
                    <tbody class="ps-comparison__group" role="rowgroup">
                        <tr class="ps-comparison__heading" role="row">
                            <th
                                colspan="<?= (int) (count($products) + 1) ?>"
                                scope="colgroup"
                                role="rowheader"
                            >
                                <?= esc_html($group->label()) ?>
                            </th>
                        </tr>
                        <?php foreach ($group->rows() as $row) : ?>
                            <tr class="ps-comparison__row" role="row">
                                <th class="ps-comparison__name" scope="row" role="rowheader">
                                    <?= esc_html($row->name()) ?>
                                </th>
                                <?php foreach ($row->cells() as $cell) : ?>
                                    <td class="ps-comparison__value" role="cell">
                                        <?php if ($cell === null) : ?>
                                            <span aria-hidden="true">&mdash;</span>
                                            <span class="screen-reader-text">
                                                <?= esc_html__('No value', 'product-specifications') ?>
                                            </span>
                                        <?php else : ?>
                                            <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Each type escapes its output. ?>
                                            <?= $cell ?>
                                        <?php endif ?>
                                    </td>
                                <?php endforeach ?>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                <?php endforeach ?>
            </table>
        </div>
    <?php endif ?>
</div>
