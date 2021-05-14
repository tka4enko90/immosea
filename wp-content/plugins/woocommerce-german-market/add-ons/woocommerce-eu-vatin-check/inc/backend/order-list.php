<?php
/**
 * Feature Name: Order List
 * Version:      1.0
 * Author:       MarketPress
 * Author URI:   http://marketpress.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
* Order List Data
*
* wp-hook manage_shop_order_posts_custom_column
* @since 3.8.2
* @param String $column
* @param Integer $post_id
* @return void
*/
function wcvat_order_list_data( $column, $post_id ) {
	
	if ( get_option( 'vat_options_backend_show_vat_info', 'on' ) == 'on' ) {
		if ( $column == 'order_total' ) {

			$order = wc_get_order( $post_id );
			echo '<span class="wcvat-data vat-info">';
			wcvat_woocommerce_order_details_after_order_table( $order );
			echo '</span>';
			return;

		}
	}

	if ( get_option( 'vat_options_backend_show_vatid', 'on' ) == 'on' ) {
		if ( $column == 'order_number' ) {
			$vat_id = get_post_meta( $post_id, 'billing_vat', true );
			if ( ! empty( $vat_id ) ) {
				echo '<span class="wcvat-data vat-id">';
				echo $vat_id;
				echo '</span>';
			}
			

		}
	}

}
