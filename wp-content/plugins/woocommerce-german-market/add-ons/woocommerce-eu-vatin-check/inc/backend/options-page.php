<?php
/**
 * Feature Name: Options Page
 * Version:      1.0
 * Author:       MarketPress
 * Author URI:   http://marketpress.com
 */

/**
* Backend Settings German Market 3.1
*
* wp-hook woocommerce_de_ui_left_menu_items
* @param Array $items
* @return Array
*/
function wcvat_woocommerce_de_ui_left_menu_items( $items ) {

	$items[ 270 ] = array( 
				'title'		=> __( 'EU VAT Number Check', 'woocommerce-german-market' ),
				'slug'		=> 'eu_vat_number_check',
				'callback'	=> 'wcvat_woocommerce_de_ui_render_options',
				'options'	=> 'yes'
		);

	return $items;
}

/**
* Render Options for global
* 
* @return void
*/
function wcvat_woocommerce_de_ui_render_options() {

	$settings = array(

		array(
			'title' => __( 'VATIN Check', 'woocommerce-german-market' ),
			'type'  => 'title',
			'desc'  => __( 'A <a href="http://en.wikipedia.org/wiki/VAT_identification_number" target="_blank">value-added tax identification number</a> is an identifier used in many countries, including the countries of the European Union, for value added tax purposes. Common abbrevations in English are <em>VAT identification number</em> or <em>VATIN</em>.<br /><br />Any VATIN allocated inside the European Union (EU) can be validated online at the official website of the European Commission. Validation confirms whether a number is currently allocated, and provides the name or other identifying details of the allocated individual or entity.<br /><br />Learn more about VATIN at the official website of the <a href="http://ec.europa.eu/taxation_customs/taxation/vat/index_en.htm" target="_blank">European Commission</a>.', 'woocommerce-german-market' ),
			'id'    => 'vat_options'
		),
		
		array(
			'title'    => __( 'Field Label', 'woocommerce-german-market' ),
			'desc_tip'     => __( 'Depending on your WordPress Theme, this label will be displayed before or after the VATIN field during checkout.', 'woocommerce-german-market' ),
			'id'       => 'vat_options_label',
			'default'  => __( 'EU VAT Identification Number (VATIN)', 'woocommerce-german-market' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
			'autoload' => FALSE
		),

		array(
			'title'    => __( 'Notice: "Tax free intracommunity delivery"', 'woocommerce-german-market' ),
			'desc_tip'     => __( 'Notice that is shown after the customer\'s order', 'woocommerce-german-market' ),
			'id'       => 'vat_options_notice',
			'default'  => __( 'Tax free intracommunity delivery', 'woocommerce-german-market' ),
			'type'     => 'textarea',
			'css'      => 'width: 400px;',
			'autoload' => FALSE
		),

		array(
			'title'    => __( 'Notice: "Tax-exempt export delivery"', 'woocommerce-german-market' ),
			'desc_tip'     => __( "Notice if the customer's billing country is a non-EU country", 'woocommerce-german-market' ),
			'id'       => 'vat_options_non_eu_notice',
			'default'  => __( 'Tax-exempt export delivery', 'woocommerce-german-market' ),
			'type'     => 'textarea',
			'css'      => 'width: 400px;',
			'autoload' => FALSE
		),
		
		array(
			'id'   => 'vat_options',
			'type' => 'sectionend',
		),

		array(
			'title' => __( 'Backend Order Table', 'woocommerce-german-market' ),
			'type'  => 'title',
			'id'	=> 'vat_backend_order_table'
		),

		array(
			'name'     => sprintf( __( 'Show %s below customer name', 'woocommerce-german-market' ), get_option( 'vat_options_label', __( 'EU VAT Identification Number (VATIN)', 'woocommerce-german-market' ) ) ),
			'desc'     => '',
			'id'       => 'vat_options_backend_show_vatid',
			'type'     => 'wgm_ui_checkbox',
			'default'  => 'on',
		),

		array(
			'name'     => __( 'Show vat info below total amount', 'woocommerce-german-market' ),
			'desc'     => '',
			'id'       => 'vat_options_backend_show_vat_info',
			'type'     => 'wgm_ui_checkbox',
			'default'  => 'on',
		),

		array(
			'id'   => 'vat_backend_order_table',
			'type' => 'sectionend',
		),

		/*
		array(
			'title' => __( 'United Kingdom (UK) in EU', 'woocommerce-german-market' ),
			'type'  => 'title',
			'id'	=> 'vat_united_kingdom'
		),

		array(
			'name'     => __( 'Treat the United Kingdom as an EU country', 'woocommerce-german-market' ),
			'id'       => 'german_market_vat_options_united_kingdom',
			'type'     => 'wgm_ui_checkbox',
			'default'  => 'on',
		),

		array(
			'id'   => 'vat_united_kingdom',
			'type' => 'sectionend',
		),
		*/

	);

	$settings = apply_filters( 'wcvat_woocommerce_de_ui_render_options', $settings );
	return( $settings );

}
