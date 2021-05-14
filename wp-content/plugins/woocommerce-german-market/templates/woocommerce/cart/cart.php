<?php
/**
 * Cart Page
 *
 * @author		WooThemes, ap, ch
 * @package		WGM/templates/woocommerce
 * @version		4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

wc_print_notices();

do_action( 'woocommerce_before_cart' );

// Enable or disable taxes
$show_taxes = apply_filters( 'woocommerce_de_print_including_tax', true );
$show_taxes = $show_taxes && ! WGM_Tax::is_kur();
?>

	<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">

		<?php do_action( 'woocommerce_before_cart_table' ); ?>

		<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
			<thead>
			<tr>
				<th class="product-remove">&nbsp;</th>
				<th class="product-thumbnail">&nbsp;</th>
				<th class="product-name"><?php echo apply_filters( 'gm_cart_column_heading_product', __( 'Product', 'woocommerce-german-market' ) ); ?></th>
				<th class="product-price"><?php echo apply_filters( 'gm_cart_column_heading_price', __( 'Price', 'woocommerce-german-market' ) ); ?></th>
				<th class="product-quantity"><?php echo apply_filters( 'gm_cart_column_heading_quantity', __( 'Quantity', 'woocommerce-german-market' ) ); ?></th>
				<th class="product-subtotal"><?php echo apply_filters( 'gm_cart_column_heading_total', __( 'Total', 'woocommerce-german-market' ) ); ?></th>
				<?php
				if ( $show_taxes ) :
					if( get_option('woocommerce_tax_display_cart') == 'excl' ) :
						$tax_incl = false;
				?>
				<th class="product-tax">
					<?php
						printf(
							/* translators: %s: tax to be added */
							apply_filters( 'gm_cart_column_heading_tax_excl', __( 'Plus %s', 'woocommerce-german-market' ) ),
							WGM_Helper::get_default_tax_label()
						);
				else :
					$tax_incl = true;
				?>
				<th class="product-tax">
					<?php
						printf(
							/* translators: %s: tax included */
							apply_filters( 'gm_cart_column_heading_tax_incl', __( 'Includes %s', 'woocommerce-german-market' ) ),
							WGM_Helper::get_default_tax_label()
						);
					?>
				</th>
				<?php endif;
				endif;
				?>
			</tr>
			</thead>
			<tbody>
			<?php do_action( 'woocommerce_before_cart_contents' ); ?>

			<?php
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

				if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {

					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
					
					?>
					<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

						<td class="product-remove">
							<?php
							echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf( '<a href="%s" class="remove" title="%s">&times;</a>', esc_url( wc_get_cart_remove_url( $cart_item_key ) ), __( 'Remove this item', 'woocommerce-german-market' ) ), $cart_item_key );
							?>
						</td>

						<td class="product-thumbnail">
							<?php
							$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

							if ( ! $_product->is_visible() )
								echo $thumbnail;
							else
								printf( '<a href="%s">%s</a>', add_query_arg( $cart_item['variation'], $_product->get_permalink() ), $thumbnail );
							?>
						</td>

						<td class="product-name" data-title="<?php esc_attr_e( 'Product', 'woocommerce-german-market' ); ?>">
							<?php

								if ( ! $product_permalink ) {
									echo apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;';
								} else {
									echo apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key );
								}

								do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );
								
								// Meta data
								echo wc_get_formatted_cart_item_data( $cart_item );

								// Backorder notification
								if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
									echo apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce-german-market' ) . '</p>', $product_id );
								}
							?>
						</td>

						<td class="product-price" data-title="<?php esc_attr_e( 'Price', 'woocommerce-german-market' ); ?>">
							<?php
							echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
							?>
						</td>

						<td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce-german-market' ); ?>">
							<?php
							if ( $_product->is_sold_individually() ) {
								$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
							} else {
								$product_quantity = woocommerce_quantity_input( array(
									'input_name'  => "cart[{$cart_item_key}][qty]",
									'input_value' => $cart_item['quantity'],
									'max_value'   => $_product->backorders_allowed() ? '' : $_product->get_stock_quantity(),
									'min_value'   => '0'
								), $_product, false );
							}

							echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
							?>
						</td>

						<td class="product-subtotal" data-title="<?php esc_attr_e( 'Subtotal', 'woocommerce-german-market' ); ?>">
							<?php
							echo WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ); // PHPCS: XSS ok.
							?>
						</td>

						<?php if ( $show_taxes ): ?>
							<td class="product-tax" data-title="<?php esc_attr_e( 'VAT', 'woocommerce-german-market' ); ?>">
							<?php

							$tax_values = WC_Tax::get_rates( $_product->get_tax_class() );

							if ( ! empty( $tax_values ) ) {

								if ( count( $tax_values ) == 1 ) {

									$tax            = @array_shift( $tax_values );
									$decimal_length = WGM_Helper::get_decimal_length( $tax[ 'rate' ] );
									$formatted_rate = number_format_i18n( (float)$tax[ 'rate' ], $decimal_length );
									if ( ( isset( $tax[ 'rate' ] ) ) && ( (float)$tax[ 'rate' ] > 0 ) && ( floatval( $cart_item[ 'line_subtotal_tax' ] > 0 ) ) ) {
										$tax_line = sprintf( '%1$s (%2$s%%)', wc_price( $cart_item[ 'line_subtotal_tax' ] ), $formatted_rate );
									} else {
										$tax_line = apply_filters( 'wgm_zero_tax_rate_message', wc_price( 0.0 ), 'cart_item' );
									}

								} else {

									$complete_tax_string = '';
									$count_taxes = 0;
									$tax_data = $cart_item[ 'line_tax_data' ];

									foreach ( $tax_values as $tax_rate_id => $tax ) {
									
										$decimal_length = WGM_Helper::get_decimal_length( $tax[ 'rate' ] );
										$formatted_rate = number_format_i18n( (float)$tax[ 'rate' ], $decimal_length );

										if ( ( isset( $tax[ 'rate' ] ) ) && ( (float)$tax[ 'rate' ] > 0 ) ) {

											$count_taxes++;
											$complete_tax_string .= sprintf( '%1$s (%2$s%%)', wc_price( $tax_data[ 'subtotal' ][ $tax_rate_id ] ), $formatted_rate );

											if ( $count_taxes < count ( $tax_values ) ) {
												$complete_tax_string .= '<br>';
											}

										}

									}

									$tax_line = $complete_tax_string;

								}								

							} else {
								
								$tax 			= array();
								$decimal_length = false;
								$formatted_rate = '';
								$tax_line 		= wc_price( 0 );
							
							}

							echo apply_filters( 'german_market_cart_tax_string', $tax_line, $cart_item );
							
							?>
							</td>
						<?php endif; ?>

					</tr>
				<?php
				}
			}

			do_action( 'woocommerce_cart_contents' );
			?>
			<tr>
				<td colspan="<?php echo apply_filters( 'wgm_cart_colspan_value', WGM_Tax::is_kur() ? 6 : 7 ); ?>" class="actions">
					<?php if ( wc_coupons_enabled() ) { ?>
						<div class="coupon">

							<label for="coupon_code"><?php _e( 'Coupon', 'woocommerce-german-market' ); ?>:</label> <input name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce-german-market' ); ?>"/> <button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce-german-market' ); ?>"><?php esc_attr_e( 'Apply coupon', 'woocommerce-german-market' ); ?></button>

							<?php do_action('woocommerce_cart_coupon'); ?>

						</div>
					<?php } ?>

					<button type="submit" class="button" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce-german-market' ); ?>"><?php esc_html_e( 'Update cart', 'woocommerce-german-market' ); ?></button>

					<?php do_action( 'woocommerce_cart_actions' ); ?>

					<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
				</td>
			</tr>

			<?php do_action( 'woocommerce_after_cart_contents' ); ?>
			</tbody>
		</table>

		<?php do_action( 'woocommerce_after_cart_table' ); ?>

	</form>

	<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>
	
	<div class="cart-collaterals">

		<?php do_action('woocommerce_cart_collaterals'); ?>

	</div>

<?php do_action( 'woocommerce_after_cart' );
