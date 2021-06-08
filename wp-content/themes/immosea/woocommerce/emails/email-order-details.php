<?php
/**
 * Order details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 3.7.0
 */

defined( 'ABSPATH' ) || exit;

$text_align = is_rtl() ? 'right' : 'left';

//do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>
<div style="height: 5px;"></div>

<div style="margin-bottom: 40px; max-width: 900px">
	<table cellspacing="0" width="900" cellpadding="6" style="width: 900px; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
		<thead>
			<tr>
				<th class="th" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Pos.', 'woocommerce' ); ?></th>
				<th class="th" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Leistungsbeschreibung', 'woocommerce' ); ?></th>
				<th class="th" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Einzelpreis netto', 'woocommerce' ); ?></th>
				<th class="th" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Menge', 'woocommerce' ); ?></th>
				<th class="th" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Summe', 'woocommerce' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			echo wc_get_email_order_items( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$order,
				array(
					'show_sku'      => $sent_to_admin,
					'show_image'    => false,
					'image_size'    => array( 32, 32 ),
					'plain_text'    => $plain_text,
					'sent_to_admin' => $sent_to_admin,
				)
			);
			?>
		</tbody>
		<tfoot>
			<?php 
				$price_no_tax = number_format( (float) $order->get_total() - $order->get_total_tax() - $order->get_total_shipping() - $order->get_shipping_tax(), wc_get_price_decimals(), '.', '' );
				$total_tax = number_format( (float) $order->get_total() - $price_no_tax , wc_get_price_decimals(), '.', '' );
			?>
			<tr style="height: 30px;"></tr>

			<tr class="subtotal">
				<td class="border-none"></td>
				<td class="border-none"></td>
				<td class="border-top" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo _e('Betrag netto', 'woocommerce'); ?></td>
				<td class="border-top" style="text-align:right;">
					<?php echo $price_no_tax.' '.get_woocommerce_currency_symbol(); ?>
				</td>
			</tr>

			<tr class="total-tax">
				<td class="border-none"></td>
				<td class="border-none"></td>
				<td class="border-none" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo _e('Umsatzsteuer 19 %', 'woocommerce'); ?></td>
				<td class="border-none" style="text-align:right;">
					<?php echo $total_tax.' '.get_woocommerce_currency_symbol();  ?>
				</td>
			</tr>

			<?php if ( $order->get_customer_note() ) { ?>
				<tr>
					<td class="border-none"></td>
					<td class="border-none"></td>
					<td class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Note:', 'woocommerce' ); ?></td>
					<td class="td" style="text-align:right;"><?php echo wp_kses_post( nl2br( wptexturize( $order->get_customer_note() ) ) ); ?></td>
				</tr>
			<?php } ?>

			<tr class="total">
				<td class="border-none"></td>
				<td class="border-none"></td>
				<td class="border-top" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo _e('Summe', 'woocommerce'); ?></td>
				<td class="border-top" style="text-align:right;">
					<?php echo $order->get_total().' '.get_woocommerce_currency_symbol(); ?>
				</td>
			</tr>
			
		</tfoot>
	</table>
</div>

<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>
