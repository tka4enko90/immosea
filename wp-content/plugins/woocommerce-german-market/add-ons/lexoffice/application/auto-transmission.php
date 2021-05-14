<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

if ( get_option( 'woocommerce_de_lexoffice_automatic_completed_order', 'off' ) == 'on' ) {
	add_action( 'woocommerce_order_status_changed', 'lexoffice_woocommerce_status_completed', 10, 3 );
}

if ( get_option( 'woocommerce_de_lexoffice_automatic_refund', 'off' ) == 'on' ) {
	add_action( 'woocommerce_create_refund', 'lexoffice_woocommerce_create_refund', 10, 2 );
}

/**
* Send Voucher to lexoffice if order is marked as completed
*
* @since 	GM 3.7.1
* @wp-hook 	woocommerce_order_status_completed
* @param 	Integer $order_id
* @return 	void
*/
function lexoffice_woocommerce_status_completed( $order_id, $old_status, $new_status ) {

	// is bulk transmission scheduled?
	$is_scheduled = get_post_meta( $order_id, '_lexoffice_woocomerce_scheduled_for_transmission', true );
	if ( ! empty( $is_scheduled ) ) {
		return;
	}

	if ( $new_status == "completed" ) {
		
		$order = wc_get_order( $order_id );

		$is_running_auto_complete = get_post_meta( $order_id, '_lexoffice_woocommerce_auto_complete_time', true );
		if ( empty( $is_running_auto_complete ) ) {
			$now = current_time( 'timestamp' );
			update_post_meta( $order_id, '_lexoffice_woocommerce_auto_complete_time', $now );
			$response = lexoffice_woocomerce_api_send_voucher( $order, false );
			delete_post_meta( $order_id, '_lexoffice_woocommerce_auto_complete_time' );
		}

	}

}

/**
* Send Voucher to lexoffice if refund is created
*
* @since 	GM 3.7.1
* @wp-hook 	woocommerce_create_refund
* @param 	WC_Order_Refund $refund
* @param 	Array $args
* @return 	void
*/
function lexoffice_woocommerce_create_refund( $refund, $args ) {

	// is bulk transmission scheduled?
	$is_scheduled = get_post_meta( $refund->get_id(), '_lexoffice_woocomerce_scheduled_for_transmission', true );
	if ( ! empty( $is_scheduled ) ) {
		return;
	}
	
	$response = lexoffice_woocommerce_api_send_refund( $refund, false );

}
