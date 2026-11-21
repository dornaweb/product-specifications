<?php

declare(strict_types=1);

use Amiut\ProductSpecs\Block\CompareButton\CompareButtonBlock;

use function Amiut\ProductSpecs\plugin;

defined('ABSPATH') || exit;

/**
 * @var array<string, mixed> $attributes
 * @var WP_Block $block
 * @var CompareButtonBlock $compareButtonBlock
 */
$compareButtonBlock = plugin()->container()->get(CompareButtonBlock::class);
$button = $compareButtonBlock->button($attributes, $block->context);

if ($button === '') {
    return;
}

?>
<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress escapes block wrapper attributes. ?>
<div <?= get_block_wrapper_attributes(['class' => 'ps-compare-button-block']) ?>>
    <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CompareButton escapes its output. ?>
    <?= $button ?>
</div>
