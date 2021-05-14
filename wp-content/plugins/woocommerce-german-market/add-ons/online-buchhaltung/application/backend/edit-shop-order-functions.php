<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

/**
* Output Icon
*
* wp-hook woocommerce_admin_order_actions_end
* @param WC_Order $order
* @return void
*/
function online_buchhaltung_1und1_edit_shop_order_icon( $order ) {
	
	$online_buchhaltung_voucher_id = get_post_meta( $order->get_id(), '_online_buchhaltung_1und1_has_transmission', true );

	// has transmission?
	$has_transmission = $online_buchhaltung_voucher_id != '';

	// manual order confirmation
	if ( get_option( 'woocommerce_de_manual_order_confirmation' ) == 'on' ) {
		if ( get_post_meta( $order->get_id(), '_gm_needs_conirmation', true ) == 'yes' ) {
			return;
		}
	}

	// is voucher still available?
	$is_valid = true;
	if ( $has_transmission ) {
		$is_valid = online_buchhaltung_1und1_api_get_vouchers_status( $online_buchhaltung_voucher_id );
	}
	
	// if not, remove post meta
	if ( ! $is_valid ) {
		delete_post_meta( $order->get_id(), '_online_buchhaltung_1und1_has_transmission' );
		$has_transmission = false;
	}
	
	// load correct icon ()
	$classes = ( $has_transmission ) ? 'online-buchhaltung-1und1-woocomerce-yes' : 'online-buchhaltung-1und1-woocomerce-x';
	$classes = apply_filters( 'online_buchhaltung_1und1_edit_shop_order_icon_classes', $classes, $has_transmission, $order );

	// markup
	?><a class="button online-buchhaltung-1und1-woocomerce-default <?php echo $classes; ?>" data-order-id="<?php echo $order->get_id(); ?>" title="<?php echo __( '1&1 Online-Buchhaltung', 'woocommerce-german-market' ); ?>"></a><?php
}

/**
* adds a small download button to the admin page for refunds
*
* @since WGM 3.0
* @access public
* @static 
* @hook wgm_refunds_actions
* @param String $string
* @param shop_order_refund $refund
* @return String
*/
function online_buchhaltung_1und1_edit_refund_icon( $actions, $refund ) {

	$online_buchhaltung_voucher_id = intval( get_post_meta( $refund->get_id(), '_online_buchhaltung_1und1_has_transmission', true ) );

	// has transmission?
	$has_transmission = $online_buchhaltung_voucher_id != 0;

	// is voucher still available?
	$is_valid = true;
	if ( $has_transmission ) {
		$is_valid = online_buchhaltung_1und1_api_get_vouchers_status( $online_buchhaltung_voucher_id );
	}

	// if not, remove post meta
	if ( ! $is_valid ) {
		delete_post_meta( $refund->get_id(), '_online_buchhaltung_1und1_has_transmission' );
		$has_transmission = false;
	}

	// load correct icon ()
	$classes = ( $has_transmission ) ? 'online-buchhaltung-1und1-woocomerce-yes' : 'online-buchhaltung-1und1-woocomerce-x';
	$classes = apply_filters( 'online_buchhaltung_1und1_edit_shop_order_icon_classes_refund', $classes, $has_transmission, $refund );

	$actions[ 'online-buchhaltung-1und1' ] = array(
		'name' 	=> '',
		'class' => 'online-buchhaltung-1und1-woocomerce-default online-buchhaltung-1und1-refund ' . $classes,
		'data'	=> array(
						'order-id' => $refund->get_parent_id(),
						'refund-id'=> $refund->get_id()
					),
		'name'	=> __( 'Send refund data to 1&1 Online-Buchhaltung', 'woocommerce-german-market' )
	);

	return $actions;
}

/**
* Enqueue scripts and styles
*
* wp-hook admin_enqueue_scripts
* @return void
*/
function online_buchhaltung_1und1_edit_shop_order_styles_and_scripts() {

	// set directories
	$assets_dir 	= untrailingslashit( plugin_dir_url( __FILE__ ) ) . '/assets';
	$styles_dir 	= $assets_dir . '/styles';
	$scripts_dir	= $assets_dir . '/scripts';

	// script debug
	$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : 'min.';

	// enqueue style
	wp_enqueue_style( 'online_buchhaltung_1und1_edit_shop_order_style', $styles_dir . '/edit-shop-order.' . $min . 'css', array(), '0.1' );

	// enqueue script
	wp_enqueue_script( 'online_buchhaltung_1und1_edit_shop_order_script', $scripts_dir . '/edit-shop-order.' . $min . 'js', array( 'jquery' ), '0.1' );

	// localize script for ajax
	wp_localize_script( 'online_buchhaltung_1und1_edit_shop_order_script', 'online_buchhaltung_ajax', 
		array(
			'url' 	=> admin_url( 'admin-ajax.php' ),
			'nonce'	=> wp_create_nonce( 'online_buchhaltung_1und1_edit_shop_order_script' ),
		)
	);

}
