<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

/**
* init actions and hooks needed for the edit-shop screen
*
* wp-hook current_screen
* @return void
*/
function sevdesk_woocommerce_edit_shop_order() {

	if ( function_exists( 'get_current_screen' ) ) {

		if ( get_current_screen()->id == 'edit-shop_order' || get_current_screen()->id == 'woocommerce_page_wgm-refunds' ) {
			
			// load functions
			$backend_dir = untrailingslashit( plugin_dir_path( __FILE__ ) );
			require_once( $backend_dir . DIRECTORY_SEPARATOR . 'edit-shop-order-functions.php' );

			// load styles and scripts, localizing script
			add_action( 'admin_enqueue_scripts', 'sevdesk_woocommerce_edit_shop_order_styles_and_scripts' );

			// add icon
			add_action( 'woocommerce_admin_order_actions_end', 'sevdesk_woocommerce_edit_shop_order_icon' );

			// add icon for refund;
			add_filter( 'wgm_refunds_actions', 'sevdesk_woocommerce_edit_refund_icon', 10, 2 );

			// add actions, filters or remove them
			do_action( 'sevdesk_woocommerce_edit_shop_order_after_init' );

		}

	}

}
