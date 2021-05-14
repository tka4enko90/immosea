<?php
/**
 * Feature Name: Options Page
 * Version:      1.0
 * Author:       MarketPress
 * Author URI:   http://marketpress.com
 */

/**
 * Adds the VAT Field to the billing adress
 *
 * @param array $address_fields
 * @return array
 */
function wcvat_woocommerce_billing_fields( $address_fields ) {

	$vat_field = array(
		'billing_vat' => array(
			'label' 	=> get_option( 'vat_options_label', __( 'EU VAT Identification Number (VATIN)', 'woocommerce-german-market' ) ),
			'class' 	=> array( 'form-row-wide' ),
			'required' 	=> apply_filters( 'wcvat_vat_field_is_required', false ),
			'priority'	=> apply_filters( 'wcvat_vat_field_priority', 49 ),
		),
	);

	$default = apply_filters( 'wcvat_woocommerce_billing_fields_vat_default', '' );
	if ( ! empty( $default ) ) {
		$vat_field[ 'billing_vat' ][ 'default' ] = $default;
	}

	if ( apply_filters( 'wcvat_insert_vat_field', true ) ) {
		$address_fields = wcvat_array_insert( $address_fields, 'billing_address_1', $vat_field );
	}

	return $address_fields;
}

/**
 * Shows the VAT in order received
 *
 * @wp-hook	woocommerce_order_details_after_customer_details
 * @param	object $order
 * @return	void
 */
function wcvat_order_details_after_customer_details( $order ) {

	$vat = get_post_meta( $order->get_id(), 'billing_vat', TRUE );
	if ( $vat )
		echo '<tr><th>' . get_option( 'vat_options_label', __( 'EU VAT Identification Number (VATIN)', 'woocommerce-german-market' ) ) . ': </th><td data-title="' . get_option( 'vat_options_label', __( 'EU VAT Identification Number (VATIN)', 'woocommerce-german-market' ) ) . '">' . $vat . '</td></tr>';
}

/**
 * Validates the user input and
 * loads the VAT Validator to
 * check it.
 *
 * @return void
 */
function wcvat_woocommerce_after_checkout_validation() {

	if ( isset( $_POST[ 'billing_vat' ] ) && $_POST[ 'billing_vat' ] != '' ) {

		// set the input
		$input = array( strtoupper( substr( $_POST[ 'billing_vat' ], 0, 2 ) ), strtoupper( substr( $_POST[ 'billing_vat' ], 2 ) ) );

		// set country
		$billing_country = isset( $_POST[ 'billing_country' ] ) ? $_POST[ 'billing_country' ] : '';

		// Validate the input
		if ( ! class_exists( 'WC_VAT_Validator' ) ) {
			require_once 'class-wc-vat-validator.php';
		}

		$validator = new WC_VAT_Validator( $input, $billing_country );

		if ( ! $validator->is_valid() ) {
			
			if ( $validator->has_errors() ) {
				
				if ( $validator->get_last_error_code() != '200' ) {
					wc_add_notice( $validator->get_last_error_message(), 'error' );
				} else {
					wc_add_notice( __( 'Please enter a valid VAT Identification Number registered in a country of the EU.', 'woocommerce-german-market' ), 'error' );
				}
			

				add_filter( 'gm_checkout_validation_first_checkout', 'wcvat_validation_first_checkout' );

			}
		}
	}
}

/**
 * If 2nd Checkout is enabled in German Market
 *
 * @wp-hook	gm_checkout_validation_first_checkout
 * @param	Integer $error_count
 * @return	Integer
 */
function wcvat_validation_first_checkout( $error_count ) {
	$error_count++;
	return $error_count;
}

/**
 * Adds the VAT Number to the E-Mails
 *
 * NOT IN USE SINCE v.3.9.1.9
 *
 * @wp-hook	woocommerce_email_order_meta_keys
 * @param	array $keys
 * @return	array $keys
 */
function wcvat_custom_checkout_field_order_meta_keys( $keys ) {
	$keys[ get_option( 'vat_options_label', __( 'EU VAT Identification Number (VATIN)', 'woocommerce-german-market' ) ) ] = 'billing_vat';
	return $keys;
}

/**
 * Notice: "Tax free intracommunity delivery" and VAT ID in emails
 *
 * @wp-hook	woocommerce_email_after_order_table
 * @param	WC_Order $order
 * @return	void
 */
function wcvat_woocommerce_email_after_order_table( $order ) { 
	
	$notice = '';
	$eu_countries = WC()->countries->get_european_union_countries();

	if ( get_post_meta( $order->get_id(), 'billing_vat', true ) != '' ) {

		if ( $order->get_billing_country() != WC()->countries->get_base_country() ) {
			
			if ( in_array( $order->get_billing_country(), $eu_countries ) ) {
				$notice = get_option( 'vat_options_notice', __( 'Tax free intracommunity delivery', 'woocommerce-german-market' ) );
			}

		}

	}

	if ( ! in_array( $order->get_billing_country(), $eu_countries ) ) {
		$notice = apply_filters( 'wcvat_woocommerce_vat_notice_not_eu', get_option( 'vat_options_non_eu_notice', __( 'Tax-exempt export delivery', 'woocommerce-german-market' ) ), $order );
	}

	// only print notice if order has no taxes!
	// (maybe someone entered an invalid vat id)
	if ( $order->get_total_tax() > 0.0 ) {
		$notice = '';
	}

	// VAT ID
	$vat_id = $order->get_meta( 'billing_vat' );
	if ( ! empty( $vat_id ) ) {

		if ( ! empty( $notice ) ) {
			$notice .= '<br />';
		}

		$notice .= apply_filters( 'wcvat_woocommerce_email_after_order_table_vat_id_markup', get_option( 'vat_options_label', __( 'EU VAT Identification Number (VATIN)', 'woocommerce-german-market' ) ) . ': ' . $vat_id, $vat_id );

	}

	if ( $notice != '' ) {
		echo apply_filters( 'wcvat_woocommerce_email_after_order_table', '<p><b>' . $notice . '</b></p>', $order );
	}

}

/**
 * Notice: "Tax free intracommunity delivery" in my-account
 *
 * @wp-hook	woocommerce_order_details_after_order_table
 * @param	WC_Order $order
 * @return	void
 */
function wcvat_woocommerce_order_details_after_order_table( $order ) {
	
	$notice = '';
	$eu_countries = WC()->countries->get_european_union_countries();

	if ( get_post_meta( $order->get_id(), 'billing_vat', true ) != '' ) {

		if ( $order->get_billing_country() != WC()->countries->get_base_country() ) {
			
			if ( in_array( $order->get_billing_country(), $eu_countries ) ) {
				$notice = get_option( 'vat_options_notice', __( 'Tax free intracommunity delivery', 'woocommerce-german-market' ) );
			}

		}

	}

	if ( ! in_array( $order->get_billing_country(), $eu_countries ) ) {
		$notice = apply_filters( 'wcvat_woocommerce_vat_notice_not_eu', get_option( 'vat_options_non_eu_notice', __( 'Tax-exempt export delivery', 'woocommerce-german-market' ) ), $order );
	}

	// only print notice if order has no taxes!
	// (maybe someone entered an invalid vat id)
	if ( $order->get_total_tax() > 0.0 ) {
		$notice = '';
	}

	if ( $notice != '' ) {
		echo apply_filters( 'wcvat_woocommerce_order_details_after_order_table', '<p>' . $notice . '</p>', $order );
	}

}

/**
 * Get "tax exempt" status of an order as String
 *
 * @param	WC_Order $order
 * @return	String
 */
function wcvat_woocommerce_order_details_status( $order ) {

	$status = '';
	$eu_countries = WC()->countries->get_european_union_countries();

	if ( get_post_meta( $order->get_id(), 'billing_vat', true ) != '' ) {

		if ( $order->get_billing_country() != WC()->countries->get_base_country() ) {
			
			if ( in_array( $order->get_billing_country(), $eu_countries ) ) {
				$status = 'tax_free_intracommunity_delivery';
			}

		}

	}

	if ( ! in_array( $order->get_billing_country(), $eu_countries ) ) {
		$status = 'tax_exempt_export_delivery';
	}

	// only print notice if order has no taxes!
	// (maybe someone entered an invalid vat id)
	if ( $order->get_total_tax() > 0.0 ) {
		$status = '';
	}

	return $status;
}

/**
 * Notice: "Tax free intracommunity delivery" in checkout
 *
 * @wp-hook	woocommerce_review_order_after_order_total
 * @return	void
 */
function wcvat_woocommerce_checkout_details_after_order_table() {

	if ( apply_filters( 'wcvat_woocommerce_checkout_details_after_order_table_disable', false ) ) {
		return;
	}

	$notice = '';
	$eu_countries 		= WC()->countries->get_european_union_countries();
	
	$billing_vat 		= WC()->checkout->get_value( 'billing_vat' );
	$billing_country 	= WC()->checkout->get_value( 'billing_country' );

	if ( ! $billing_vat ) {
		if ( isset( $_REQUEST[ 'post_data' ] ) ) {
			$post_data = array();
			parse_str( $_REQUEST[ 'post_data' ], $post_data );
			if ( isset( $post_data[ 'billing_vat' ] ) ) {
				$billing_vat = $post_data[ 'billing_vat' ];
			}
		}
	}

	if ( ! $billing_country ) {
		if ( isset( $_REQUEST[ 'post_data' ] ) ) {
			$post_data = array();
			parse_str( $_REQUEST[ 'post_data' ], $post_data );
			if ( isset( $post_data[ 'billing_country' ] ) ) {
				$billing_country = $post_data[ 'billing_country' ];
			}
		}
	}

	if ( $billing_vat  != '' ) {

		if ( WC()->countries->get_base_country() != $billing_country ) {
			
			if ( in_array( $billing_country, $eu_countries ) ) {
				$notice = get_option( 'vat_options_notice', __( 'Tax free intracommunity delivery', 'woocommerce-german-market' ) );
			}

		}

	}

	if ( ! in_array( $billing_country, $eu_countries ) ) {
		$notice = apply_filters( 'wcvat_woocommerce_vat_notice_not_eu_checkout', get_option( 'vat_options_non_eu_notice', __( 'Tax-exempt export delivery', 'woocommerce-german-market' ) ), WC()->checkout );
	}

	// only print notice if order has no taxes!
	// (maybe someone entered an invalid vat id)
	if ( WC()->cart->get_total_tax() > 0.0 ) {
		$notice = '';
	}

	if ( $notice != '' ) {
		echo '<tr class="wcvat-notice-german-market"><th colspan="2" class="wcvat-notice-german-market-th">';
		echo apply_filters( 'wcvat_woocommerce_order_details_after_order_table_checkout', $notice, WC()->chekcout );
		echo '</th></tr>';
	}

}

/**
 * Display the VAT Field in the Backend
 *
 * @param object $order
 *
 * @return void
 */
function wcvat_woocommerce_admin_order_data_after_billing_address( $order ) {
	
	if ( get_post_meta( $order->get_id(), 'billing_vat', TRUE ) != '' ) {
		echo '<p style="width: 100%; float: left;"><strong>' . get_option( 'vat_options_label', __( 'EU VAT Identification Number (VATIN)', 'woocommerce-german-market' ) ) . ':</strong><br />' . get_post_meta( $order->get_id(), 'billing_vat', TRUE ) . '</p>';
	}

}

/**
 * Save the VAT Number at the order
 *
 * @param int $order_id
 *
 * @return void
 */
function wcvat_woocommerce_checkout_update_order_meta( $order, $posted ) {
	
	if ( ! empty( $posted[ 'billing_vat' ] ) ) {
		$vat = sanitize_text_field( $posted[ 'billing_vat' ] );
    	$order->update_meta_data( 'billing_vat', $vat );
		$order->save();
	}
}

/**
 * AJAX callback to check the VAT
 *
 * @wp-hook	wp_ajax_wcvat_check_vat, wp_ajax_nopriv_wcvat_check_vat
 * @return	void
 */
function wcvat_check_vat() {

	// get the billing vat
	$billing_vat = $_REQUEST[ 'vat' ];
	$raw_billing_vat = strtoupper( substr( $billing_vat, 0, 2 ) ) . strtoupper( substr( $billing_vat, 2 ) );
	$billing_vat = array( strtoupper( substr( $billing_vat, 0, 2 ) ), strtoupper( substr( $billing_vat, 2 ) ) );
	$billing_country = $_REQUEST[ 'country' ];

	$response = array( 'success' => '', 'data' => '' );

	// No need to validate if field is empty
	// The following block code does not work as expected, cause setting the sessions takes too long
	// see wcvat_woocommerce_before_calculate_totals, first comments, the first code block in this functions fixes the problem
	if ( trim( $_REQUEST[ 'vat' ] ) == '' ) {
		
		$response[ 'success' ] = FALSE;
		$response[ 'data' ]    = __( 'Field is empty.', 'woocommerce-german-market' );

		// add taxes
		WGM_Session::add( 'eu_vatin_check_exempt', false );
		WGM_Session::remove( 'eu_vatin_check_billing_vat' );

		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			delete_user_meta( $current_user->ID, 'billing_vat' );
		}

		WC()->customer->set_is_vat_exempt( false );

		echo json_encode( $response );
		exit;
	}

	// validate the billing_vat
	if ( ! class_exists( 'WC_VAT_Validator' ) ) {
		require_once 'class-wc-vat-validator.php';
	}

	$validator = new WC_VAT_Validator( $billing_vat, $billing_country );

	if ( $validator->is_valid() === FALSE ) {

		// add taxes
		WGM_Session::add( 'eu_vatin_check_exempt', false );
		WGM_Session::remove( 'eu_vatin_check_billing_vat' );

		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			delete_user_meta( $current_user->ID, 'billing_vat' );
		}

		$response[ 'success' ] = FALSE;

	} else {
		
		if ( $billing_country == WC()->countries->get_base_country() ) {

			// add taxes
			WGM_Session::add( 'eu_vatin_check_exempt', false );
			WGM_Session::remove( 'eu_vatin_check_billing_vat' );

			if ( is_user_logged_in() ) {
				$current_user = wp_get_current_user();
				delete_user_meta( $current_user->ID, 'billing_vat' );
			}

		} else {

			// remove taxes
			WGM_Session::add( 'eu_vatin_check_exempt', true );
			WGM_Session::add( 'eu_vatin_check_billing_vat', $billing_vat );

			if ( is_user_logged_in() ) {
				$current_user = wp_get_current_user();
				update_user_meta( $current_user->ID, 'billing_vat', $raw_billing_vat );
			}

		}

		// output response
		$response[ 'success' ] = TRUE;
	}

	echo json_encode( $response );
	exit;
}

/**
 * Check VAT exempt with WGM_Session Class
 *
 * @wp-hook	woocommerce_before_calculate_totals
 * @return	void
 */
function wcvat_woocommerce_before_calculate_totals() {
	
	// if billing vat is empty => not vat exempted
	// in most cases when switching the country to base country
	// the session variable is set to slow, so we need this check
	if ( isset( $_REQUEST[ 'post_data' ] ) ) {
		
		parse_str( $_REQUEST[ 'post_data' ], $post_data );
		
		$billing_vat_is_empty = true;

		if ( isset( $post_data[ 'billing_vat' ] ) ) {

			if ( $post_data[ 'billing_vat' ] != '' ) {
				$billing_vat_is_empty = false;
			}

		}

		if ( $billing_vat_is_empty ) {
			WC()->customer->set_is_vat_exempt( false );
			WGM_Session::add( 'eu_vatin_check_exempt', false );
			WGM_Session::remove( 'eu_vatin_check_billing_vat' );
			return;
		}
		
	}

	if ( WGM_Session::get( 'eu_vatin_check_exempt' ) ) {
			WC()->customer->set_is_vat_exempt( true );
	}

}

/**
 * Add UK to european union countries by backend option
 *
 * @wp-hook	woocommerce_european_union_countries
 * @param Array $countries
 * @return Array
 */
function wcvat_woocommerce_european_union_countries_uk( $countries ) {

	if ( get_option( 'german_market_vat_options_united_kingdom', 'on' ) == 'on' ) {
		$countries[] = 'GB';
	}

	return $countries;

}
