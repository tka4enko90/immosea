<?php
/**
 * Feature Name: Script Functions
 * Version:      1.0
 * Author:       MarketPress
 * Author URI:   http://marketpress.com/
 */

/**
 * Enqueue the scripts.
 *
 * @wp-hook wp_enqueue_scripts
 * @return  void
 */
function wcvat_wp_enqueue_scripts() {

	$scripts = wcvat_get_scripts();

	if ( ! is_array( $scripts ) ) {
		return;
	}

	foreach ( $scripts as $handle => $script ) {

		// load script
		wp_enqueue_script(
			$handle,
			$script[ 'src' ],
			$script[ 'deps' ],
			$script[ 'version' ],
			$script[ 'in_footer' ]
		);

		// check localize
		if ( ! empty( $script[ 'localize' ] ) )
			foreach ( $script[ 'localize' ] as $localize_id => $vars )
				wp_localize_script( $handle, $localize_id, $vars );
	}
}

/**
 * Returning our Scripts
 *
 * @return  array
 */
function wcvat_get_scripts(){

	$scripts = array();
	$suffix = wcvat_get_script_suffix();
	$base_location = wc_get_base_location();

	// support for wirecard plugin
	$wirecard_support = apply_filters( 'wcvat_frontend_script_support', function_exists( 'wirecard_init_payment_gateway' ) ? '-wirecard' : '' );

	// adding the main-js
	$scripts[ 'wcvat-js' ] = array(
		'src'       => apply_filters( 'wcvat_frontend_script_src', wcvat_get_asset_directory_url( 'js' ) . 'frontend' . $wirecard_support . $suffix . '.js' ),
		'deps'      => array( 'jquery' ),
		'version'   => '3.5.1',
		'in_footer' => TRUE,
		'localize'  => array(
			'wcvat_script_vars' 							=> array(
				'ajaxurl' 									=> admin_url( 'admin-ajax.php' ),
				'error_badge' 								=> '<span class="error-badge">' . __( 'The VATIN is not valid!', 'woocommerce-german-market' ) . '</span>',
				'correct_badge' 							=> '<span class="correct-badge">&nbsp;</span>',
				'spinner' 									=> '<span class="spinner-badge">' . __( 'Validating ...', 'woocommerce-german-market' ) . '</span>',
				'base_country'								=> $base_location[ 'country' ],
				'base_country_hide' 						=> apply_filters( 'wcvat_hide_vat_field_billing_country_equal_shop_country', true ),						// set to false: vat field is always shown
				'show_for_basecountry_hide_eu_countries' 	=> apply_filters( 'wcvat_show_vat_field_biling_country_equal_shop_country_hide_non_eu_countries', false ),	// set to true: vat field is shown for alle eu countries incl. base country, not shown for non-eu-countries
				'non_eu_country_hide'						=> apply_filters( 'wcvat_hide_vat_field_non_eu_country', true ),
				'trigger_update_checkout'					=> apply_filters( 'wcvat_trigger_update_checkout', true ),
				'eu_countries'								=> WC()->countries->get_european_union_countries(),
			),
		),
	);

	return apply_filters( 'wcvat_get_scripts', $scripts );
}
