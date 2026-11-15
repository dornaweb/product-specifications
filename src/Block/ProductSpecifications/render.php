<?php

declare(strict_types=1);

use Amiut\ProductSpecs\Block\ProductSpecifications\ProductSpecificationsBlock;

use function Amiut\ProductSpecs\plugin;

defined('ABSPATH') || exit;

/**
 * @var array<string, mixed> $attributes
 * @var ProductSpecificationsBlock $productSpecificationsBlock
 */
$productSpecificationsBlock = plugin()->container()->get(ProductSpecificationsBlock::class);
$table = $productSpecificationsBlock->table($attributes);

if ($table->isEmpty()) {
    return;
}

?>
<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress escapes block wrapper attributes. ?>
<div <?= get_block_wrapper_attributes(['class' => 'ps-product-specifications']) ?>>
    <table class="ps-product-specifications__table">
        <?php foreach ($table->groups() as $group) : ?>
            <tbody class="ps-product-specifications__group">
                <tr class="ps-product-specifications__heading">
                    <th colspan="2" scope="colgroup" class="ps-product-specifications__label">
                        <?= esc_html($group->label()) ?>
                    </th>
                </tr>
                <?php foreach ($group->rows() as $row) : ?>
                    <tr class="ps-product-specifications__row">
                        <th scope="row" class="ps-product-specifications__name"><?= esc_html($row->name()) ?></th>
                        <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Each type escapes its output. ?>
                        <td class="ps-product-specifications__value"><?= $row->value() ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        <?php endforeach ?>
    </table>
</div>
