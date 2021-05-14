<?php
/* 
 * Add-on Name:	1und1 Online-Buchhaltung
 * Description:	1und1 Online-Buchhaltung API for Woocommerce
 * Version:		1.0
 * Author:		MarketPress
 * Author URI:	http://marketpress.com
 * Licence:		GPLv3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

if ( ! function_exists( 'online_buchhaltung_1und1_init' ) ) {

	/**
	* init
	*
	* @return void
	*/
	function online_buchhaltung_1und1_init() {

		if ( is_admin() ) {

			$backend_dir = untrailingslashit( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'backend';
			
			// stuff that is only needed in the shop order table
			require_once( $backend_dir . DIRECTORY_SEPARATOR . 'edit-shop-order.php' );
			add_action( 'current_screen', 'online_buchhaltung_1und1_edit_shop_order' );
			
			// load api
			require_once( $backend_dir . DIRECTORY_SEPARATOR . 'api.php' );

			// settings
			require_once( $backend_dir . DIRECTORY_SEPARATOR . 'settings.php' );
			add_filter( 'woocommerce_de_ui_left_menu_items', 'online_buchhaltung_1und1_de_ui_left_menu_items' );

			// ajax handler
			if ( function_exists( 'curl_init' ) ) {

				require_once( $backend_dir . DIRECTORY_SEPARATOR . 'ajax-handler.php' );
				add_action( 'wp_ajax_online_buchhaltung_1und1_edit_shop_order', 'online_buchhaltung_1und1_edit_shop_order_ajax' );
				add_action( 'wp_ajax_online_buchhaltung_1und1_edit_shop_order_refund', 'online_buchhaltung_1und1_edit_shop_order_ajax_refund' );
				
			}
				
		}

	}
	
	online_buchhaltung_1und1_init();

}
