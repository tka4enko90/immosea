<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class German_Market_Theme_Compatibility_Avada {

	/**
	* Theme Avada Support
	*
	* @access public
	* Tested with Theme Version 5.7.2
	* @wp-hook after_setup_theme
	* @return void
	*/
	public static function init() {
		add_filter( 'woocommerce_cart_item_name', array( __CLASS__, 'remove_double_digital' ), 99, 3 );

		// removed in 3.8.1
		//add_filter( 'woocommerce_pay_order_button_html', array( $this, 'pay_for_order_page_checkboxes' ) );
	}

	/**
	* Theme Avada Support: Checkboxes on pay for order page
	*
	* @access public
	* @wp-hook woocommerce_pay_order_button_html
	* @param String $markup
	* @return String
	*/
	public static function pay_for_order_page_checkboxes( $markup ) {

		if ( is_wc_endpoint_url( 'order-pay' ) ) {

			$markup = WGM_Template::add_review_order() . $markup;

		}

		return $markup;

	}

	/**
	* Theme Avada Support: Double "[Digital]" during checkout, very simple solution
	*
	* @access public
	* @wp-hook woocommerce_cart_item_name
	* @return void
	*/
	public static function remove_double_digital( $title, $cart_item, $cart_item_key ) {
		return str_replace( '[Digital] [Digital]', '[Digital]', $title );
	}

}
