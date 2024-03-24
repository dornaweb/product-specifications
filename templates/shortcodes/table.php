<?php
/**
 * View template for spec. table shortcode
 *
 * @author Am!n <www.dornaweb.com>
 * @package Wordpress
 * @subpackage Product Specifications Table Plugin
 * @link http://www.dornaweb.com
 * @license GNU General Public License v2 or later, http://www.gnu.org/licenses/gpl-2.0.html
 * @version 0.1
*/

if( !$table ) return;
echo '<div class="dwspecs-product-table">';
foreach( $table as $table_group ):
	if( $table_group['attributes'] ) :
		$attributes = $table_group['attributes']; ?>
	<div class="dwspecs-product-table-group">
		<div class="group-title"><?php echo esc_html($table_group['group_name']); ?></div>

		<table>
			<?php foreach( $attributes as $attr ) : ?>
			<tr>
				<td><?php echo esc_html($attr['attr_name']); ?></td>
				<td <?php if( $attr['value'] == 'yes' || $attr['value'] == 'no' ) echo 'class="'. esc_attr($attr['value']) .'"'; ?>>

					<?php
					if( $attr['value'] == 'yes' ):
						echo apply_filters( 'dwspecs_table_value_output', '<svg class="yes" width="32" height="32" viewBox="0 0 32 32">
							<path d="M29.839 10.107q0 0.714-0.5 1.214l-15.357 15.357q-0.5 0.5-1.214 0.5t-1.214-0.5l-8.893-8.893q-0.5-0.5-0.5-1.214t0.5-1.214l2.429-2.429q0.5-0.5 1.214-0.5t1.214 0.5l5.25 5.268 11.714-11.732q0.5-0.5 1.214-0.5t1.214 0.5l2.429 2.429q0.5 0.5 0.5 1.214z"></path>
						</svg>', $attr['value'] );

						elseif( $attr['value'] == 'no' ):
							echo apply_filters( 'dwspecs_table_value_output', '<svg class="no" width="25" height="32" viewBox="0 0 25 32">
							<path d="M23.179 23.607q0 0.714-0.5 1.214l-2.429 2.429q-0.5 0.5-1.214 0.5t-1.214-0.5l-5.25-5.25-5.25 5.25q-0.5 0.5-1.214 0.5t-1.214-0.5l-2.429-2.429q-0.5-0.5-0.5-1.214t0.5-1.214l5.25-5.25-5.25-5.25q-0.5-0.5-0.5-1.214t0.5-1.214l2.429-2.429q0.5-0.5 1.214-0.5t1.214 0.5l5.25 5.25 5.25-5.25q0.5-0.5 1.214-0.5t1.214 0.5l2.429 2.429q0.5 0.5 0.5 1.214t-0.5 1.214l-5.25 5.25 5.25 5.25q0.5 0.5 0.5 1.214z"></path>
						</svg>', $attr['value'] );

					else :
						echo wp_kses_post(apply_filters( 'dwspecs_table_value_output', nl2br( $attr['value'] ), $attr['value'] ));
					endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</table>
	</div>
<?php
	endif;
endforeach;
echo '</div><!-- .dwspecs-product-table -->';
