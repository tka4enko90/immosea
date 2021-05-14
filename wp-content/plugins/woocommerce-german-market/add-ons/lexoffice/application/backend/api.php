<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
* API - send voucher
*
* @param WC_ORDER $order
* @return String ("SUCCESS" or "ERROR: {your error Message}")
*/
function lexoffice_woocomerce_api_send_voucher( $order, $show_errors = true ) {

	///////////////////////////////////
	// can we start?
	///////////////////////////////////
	if ( ! apply_filters( 'woocommerce_de_lexoffice_force_transmission_even_if_not_completed', false ) ) {

		if ( $order->get_status() != 'completed' ) {
			if ( $show_errors ) {
				return __( '<b>ERROR:</b> Order status is not completed. You can only send data to lexoffice if the order status is completed.', 'woocommerce-german-market' );
			} else {
				return;
			}
		}

	}

	if ( ! class_exists( 'WP_WC_Invoice_Pdf_Create_Pdf' ) ) {
		if ( $show_errors ) {
			echo __( '<b>ERROR:</b> Modul Invoice PDF of WooCommerce German Market is not enabled.', 'woocommerce-german-market' );
			exit();
		} else {
			return;
		}
	}

	$order_lexoffice_status = get_post_meta( $order->get_id(), '_lexoffice_woocomerce_has_transmission', true );

	add_action( 'woocommerce_de_lexoffice_api_before_send', $order );

	if ( $order_lexoffice_status == '' ) {
		$response = lexoffice_woocomerce_api_send_voucher_post( $order, $show_errors );
	} else {
		$response = lexoffice_woocomerce_api_send_voucher_put( $order, $show_errors );
	}

	add_action( 'woocommerce_de_lexoffice_api_after_send', $order );

	$response_array = json_decode( $response, true );

	// evaluate response
	if ( ! isset ( $response_array[ 'id' ] ) ) {
		if ( $show_errors ) {
			return '<b>' . __( 'ERROR', 'woocommerce-german-market' ) . ':</b> ' . lexoffice_woocomerce_get_error_text( $response );
		} else {
			return;
		}
	}

	// save lexoffice id as post meta
	update_post_meta( $order->get_id(), '_lexoffice_woocomerce_has_transmission', $response_array[ 'id' ] );

	///////////////////////////////////
	// send invoice pdf to lexoffice
	///////////////////////////////////
	$response_invoice_pdf = lexoffice_woocomerce_api_upload_invoice_pdf( $response_array[ 'id' ], $order, false, $show_errors );
	$response_array = json_decode( $response_invoice_pdf, true );

	return 'SUCCESS';

}

/**
* API - create voucher, post method
*
* @param WC_ORDER $order
* @return String
*/
function lexoffice_woocomerce_api_send_voucher_post( $order, $show_errors = true ) {

	$curl = curl_init();

	curl_setopt_array( $curl, 

		array(
		  	CURLOPT_URL => "https://api.lexoffice.io/v1/vouchers",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => lexoffice_woocomerce_api_order_to_curlopt_postfields( $order, $show_errors ),
			CURLOPT_HTTPHEADER => array(
			    "accept: application/json",
			    "authorization: Bearer " . lexoffice_woocomerce_api_get_bearer(),
			    "cache-control: no-cache",
			    "content-type: application/json",
			  ),
		)

	);

	return curl_exec( $curl );
}

/**
* API - update voucher, put method
*
* @param WC_ORDER $order || Refund
* @return String
*/
function lexoffice_woocomerce_api_send_voucher_put( $order, $show_errors = true ) {

	$voucher_id = get_post_meta( $order->get_id(), '_lexoffice_woocomerce_has_transmission', true );
	$response_array = lexoffice_woocommerce_api_get_vouchers_status( $voucher_id, false );

	if ( isset( $response_array[ 'error' ] ) && $response_array[ 'error' ] == 'Not Found' || empty( $response_array ) || is_null( $response_array ) ) {
		return lexoffice_woocomerce_api_send_voucher_post( $order, $show_errors );
	}

	$new_data_for_lexoffice = lexoffice_woocomerce_api_order_to_curlopt_postfields( $order, $show_errors );
	$new_data_for_lexoffice = json_decode( $new_data_for_lexoffice );
	$new_data_for_lexoffice->version 	= $response_array[ 'version' ];
	$new_data_for_lexoffice->id 		= $response_array[ 'id' ];
	if ( isset( $response_array[ 'organizationId' ] ) ) {
		$new_data_for_lexoffice->organizationId =  $response_array[ 'organizationId' ];
	}

	ini_set( 'serialize_precision', -1 );
	$new_data_for_lexoffice = json_encode( $new_data_for_lexoffice, JSON_PRETTY_PRINT );

	$curl = curl_init();

	curl_setopt_array( $curl, 

		array(
		  	CURLOPT_URL => "https://api.lexoffice.io/v1/vouchers/" . $voucher_id,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "PUT",
			CURLOPT_POSTFIELDS => $new_data_for_lexoffice,
			CURLOPT_HTTPHEADER => array(
			    "accept: application/json",
			    "authorization: Bearer " . lexoffice_woocomerce_api_get_bearer(),
			    "cache-control: no-cache",
			    "content-type: application/json",
			  ),
		)

	);

	$response_post 	= curl_exec( $curl );
	$response_array = json_decode( $response_post, true );

	if ( ! isset( $response_array[ 'id' ] ) ) {

		if ( isset( $response_array[ 'IssueList' ][ 0 ][ 'i18nKey' ] ) ) {
			if ( $response_array[ 'IssueList' ][ 0 ][ 'i18nKey' ] == 'action_forbidden_voucher_state_or_payment' ) {

				if ( $show_errors ) {
					echo '<b>' . __( 'ERROR', 'woocommerce-german-market' ) . ':</b> ' . __( 'The voucher could not be updated. The voucher is may connected with a payment or has been marked as finished (transfered to tax authorities). To update the voucher you can try to remove the connected payment. If the voucher has been transfered to tax authorities it is bocked and you cannot update the voucher.', 'woocommerce-german-market' );
					
					exit();
				} else {
					return;
				}
				
			}
		}


	}

	return $response_post;

}

/**
* API - send refund
*
* @param WC_ORDER $order
* @return String ("SUCCESS" or "ERROR: {your error Message}")
*/
function lexoffice_woocommerce_api_send_refund( $refund, $show_errors = true ) {

	$refund_lexoffice_status = get_post_meta( $refund->get_id(), '_lexoffice_woocomerce_has_transmission', true );

	$order_id 				= $refund->get_parent_id();
	$order 					= wc_get_order( $order_id );
	
	do_action( 'woocommerce_de_lexoffice_api_before_send_refund', $order, $refund );

	if ( $refund_lexoffice_status == '' ) {
		$response = lexoffice_woocomerce_api_send_refund_post( $refund, $show_errors );
	} else {
		$response = lexoffice_woocomerce_api_send_refund_put( $refund, $show_errors );
	}

	do_action( 'woocommerce_de_lexoffice_api_after_send_refund', $order, $refund );
	
	$response_array = json_decode( $response, true );

	// evaluate response
	if ( ! isset ( $response_array[ 'id' ] ) ) {
		if ( $show_errors ) {
			return '<b>' . __( 'ERROR', 'woocommerce-german-market' ) . ':</b> ' . lexoffice_woocomerce_get_error_text( $response );
		} else {
			return;
		}
	}

	// save sevdesk id as post meta
	update_post_meta( $refund->get_id(), '_lexoffice_woocomerce_has_transmission', $response_array[ 'id' ] );

	///////////////////////////////////
	// send refund pdf to lexoffice
	///////////////////////////////////
	$response_invoice_pdf = lexoffice_woocomerce_api_upload_invoice_pdf( $response_array[ 'id' ], $refund, true, $show_errors );
	$response_array = json_decode( $response_invoice_pdf, true );

	return 'SUCCESS';

}

/**
* API - create refund voucher, post method
*
* @param WC_ORDER $order
* @return String
*/
function lexoffice_woocomerce_api_send_refund_post( $refund, $show_errors = true ) {

	$curl = curl_init();

	curl_setopt_array( $curl, 

		array(
		  	CURLOPT_URL => "https://api.lexoffice.io/v1/vouchers",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => lexoffice_woocomerce_api_refund_to_curlopt_postfields( $refund, null, $show_errors ),
			CURLOPT_HTTPHEADER => array(
			    "accept: application/json",
			    "authorization: Bearer " . lexoffice_woocomerce_api_get_bearer(),
			    "cache-control: no-cache",
			    "content-type: application/json",
			  ),
		)

	);

	return curl_exec( $curl );
}

/**
* API - update refund, put method
*
* @param WC_ORDER $order || Refund
* @return String
*/
function lexoffice_woocomerce_api_send_refund_put( $refund, $show_errors = true ) {

	$voucher_id = get_post_meta( $refund->get_id(), '_lexoffice_woocomerce_has_transmission', true );
	$response_array = lexoffice_woocommerce_api_get_vouchers_status( $voucher_id, false );

	if ( isset( $response_array[ 'error' ] ) && $response_array[ 'error' ] == 'Not Found' || empty( $response_array ) || is_null( $response_array ) ) {
		return lexoffice_woocomerce_api_send_refund_post( $refund, $show_errors );
	}

	$new_data_for_lexoffice = lexoffice_woocomerce_api_refund_to_curlopt_postfields( $refund, null, $show_errors );
	$new_data_for_lexoffice = json_decode( $new_data_for_lexoffice );
	$new_data_for_lexoffice->version 	= $response_array[ 'version' ];
	$new_data_for_lexoffice->id 		= $response_array[ 'id' ];
	if ( isset( $response_array[ 'organizationId' ] ) ) {
		$new_data_for_lexoffice->organizationId =  $response_array[ 'organizationId' ];
	}

	ini_set( 'serialize_precision', -1 );
	$new_data_for_lexoffice = json_encode( $new_data_for_lexoffice, JSON_PRETTY_PRINT );

	$curl = curl_init();

	curl_setopt_array( $curl, 

		array(
		  	CURLOPT_URL => "https://api.lexoffice.io/v1/vouchers/" . $voucher_id,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "PUT",
			CURLOPT_POSTFIELDS => $new_data_for_lexoffice,
			CURLOPT_HTTPHEADER => array(
			    "accept: application/json",
			    "authorization: Bearer " . lexoffice_woocomerce_api_get_bearer(),
			    "cache-control: no-cache",
			    "content-type: application/json",
			  ),
		)

	);

	return curl_exec( $curl );

}

/**
* Create Curlopt Postfields from a refund
*
* @param WC_Order_Refund $refund
* @param String $file
* @return String (JSON formated)
*/
function lexoffice_woocomerce_api_refund_to_curlopt_postfields( $refund, $file = null, $show_errors = true ) {

	// init data
	$order_id 				= $refund->get_parent_id();
	$order 					= wc_get_order( $order_id );
	$complete_refund_amount = $refund->get_amount() * ( -1 );
	$item_sum_refunded 		= 0.0;
	$item_tax_refunded 		= 0.0;
	$refund_reason 			= $refund->get_reason() == '' ? '' : sprintf( __( '(%s)', 'woocommerce-german-market' ), $refund->get_reason() );
	$voucher_items 			= array();
	$categoryId	 			= get_option( 'woocommerce_de_kleinunternehmerregelung' ) == 'on' ? '7a1efa0e-6283-4cbf-9583-8e88d3ba5960': '8f8664a8-fd86-11e1-a21f-0800200c9a66';
	$currency				= $order->get_currency();

	if ( get_option( 'woocommerce_de_lexoffice_contacts', 'collective_contact' ) == 'lexoffice_contacts' ) {
		if ( get_option( 'woocommerce_de_kleinunternehmerregelung', 'off' ) != 'on' ) {
			if ( function_exists( 'wcvat_woocommerce_order_details_status' ) ) {
				$tax_exempt_status = wcvat_woocommerce_order_details_status( $order );
				if ( $tax_exempt_status == 'tax_free_intracommunity_delivery' ) {
					
					if ( ! empty( $order->get_billing_company() ) ) {
						$categoryId = '9075a4e3-66de-4795-a016-3889feca0d20';
					}
					
					} else if ( $tax_exempt_status == 'tax_exempt_export_delivery' ) {
						if ( ! empty( $order->get_billing_company() ) || apply_filters( 'lexoffice_woocommerce_use_tax_exempt_export_use_customer_as_company', false ) ) {
						$categoryId = '93d24c20-ea84-424e-a731-5e1b78d1e6a9';
					}
				}
			}
		}
	}

	// Check Currency, only EUR is supported
	$currency = $order->get_currency();
	if ( $currency != 'EUR' ) {
		if ( $show_errors ) {
			echo sprintf( __( '"%s" is not a supported currency.', 'woocommerce-german-market' ), $currency );
			exit();
		} else {
			return;
		}
		
	}

	// allowed tax rates, values are net sums of items
	$allowed_tax_rates = apply_filters( 'lexoffice_woocomerce_api_allowed_tax_rates',
		array(	0.0		=> 0.0,
				5.0		=> 0.0,
				7.0		=> 0.0,
				16.0	=> 0.0,
				19.0 	=> 0.0,
		)
	);

	$user = $order->get_user();
	if ( ! $user ) {
		$user_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
	} else {
		$user_name = $user->display_name;
	}

	$voucher_items = array();

	///////////////////////////////////
	// build voucher positions, 1st: order items
	///////////////////////////////////
	foreach ( $refund->get_items() as $item ) {
		
		if ( ! ( abs( $refund->get_line_total( $item, true, true ) ) > 0.0 ) ) {
			continue;
		} 

		if ( abs( $refund->get_line_total( $item, false, true ) ) > 0 ) {
			$tax_rate = round( $refund->get_line_tax( $item ) / $refund->get_line_total( $item, false, false ) * 100, 1 );
		} else {
			$tax_rate = 0.0;
		}

		if ( ! isset( $allowed_tax_rates[ floatval( $tax_rate ) ] ) ) {

			// Fix Problems with discounts and wrong tax_amount, eg:
			// WooCommerce says: Total Net: 0.04, Tax: 0.01 => 25% rate, nonsense.
			// try to find tax amount without discounts
			$line_total_net 	= floatval( $refund->get_line_subtotal( $item, false, false ) );
			$line_total_gross 	= floatval( $refund->get_line_subtotal( $item, true, false ) );

			if ( abs( $line_total_net ) > 0 ) {
				
				$maybe_tax_rate 			= round( ( $line_total_gross / $line_total_net - 1), 2 ) * 100;
				$max_tax_rate_not_rounded 	= ( $line_total_gross / $line_total_net - 1 ) * 100;		

				if ( isset( $allowed_tax_rates[ floatval( $maybe_tax_rate ) ] ) ) {
					$tax_rate = abs( $maybe_tax_rate );
				}

			}

		}
		
		// fix problems with small amounts 1
		if ( ! isset( $allowed_tax_rates[ floatval( $tax_rate ) ] ) ) {
			$item_data = $item->get_taxes();
			if ( isset( $item_data[ 'subtotal' ] ) && is_array( $item_data[ 'subtotal' ] ) && count( $item_data[ 'subtotal' ] ) == 1 ) {
				
				if ( function_exists( 'array_key_first' ) ) {
					$tax_rate_id = array_key_first( $item_data[ 'subtotal' ] );
				} else {
					foreach ( $item_data[ 'subtotal' ] as $key => $value ) {
						$tax_rate_id = $key;
						break;
					}
				}

				$maybe_tax_rate = intval( str_replace( '%', '', WC_Tax::get_rate_percent( $tax_rate_id ) ) );
				if ( isset( $allowed_tax_rates[ floatval( $maybe_tax_rate ) ] ) ) {
					$tax_rate = $maybe_tax_rate;
				}
			}
		}
		
		// fix problems with small amounts 2
		if ( ! isset( $allowed_tax_rates[ floatval( $tax_rate ) ] ) ) {

			$order_taxes =  $order->get_taxes();
			foreach ( $order_taxes as $tax ) {
				$tax_rate_percent = intval(  WC_Tax::get_rate_percent( $tax->get_rate_id() ) );
				if ( abs( $tax_rate_percent - $max_tax_rate_not_rounded ) <  1.0 ) {
					$tax_rate = $tax_rate_percent;
				}
			}
		}
		
		// only 0%, 7% and 19% are supported
		if ( ! isset( $allowed_tax_rates[ floatval( $tax_rate ) ] ) ) {
			if ( $show_errors ) {
				echo sprintf( __( '<b>ERROR:</b> Unsupported tax rate: %s.', 'woocommerce-german-market' ), $tax_rate . '%' );
				exit();
			} else {
				return;
			}
		}

		$voucher_items[] = array(
			'amount'			=> abs( $refund->get_line_total( $item, true ) ),
			'taxAmount'			=> abs( $refund->get_line_tax( $item ) ),
			'taxRatePercent'	=> $tax_rate,
			'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_item', $categoryId, $item, $order )
		);

		$item_sum_refunded += abs( $refund->get_line_total( $item, true, true ) );
		$item_tax_refunded += abs( $refund->get_line_tax( $item ) );

	}

	///////////////////////////////////
	// Shipping
	///////////////////////////////////
	$shipping = floatval( $refund->get_total_shipping() );
	$shipping_tax = floatval( $refund->get_shipping_tax() );
	$shipping_gross = floatval( $shipping + $shipping_tax );

	if ( abs( $shipping_gross ) > 0.0 && abs( $shipping ) > 0.0 ) {
		
		$shipping_rate = round( $shipping_tax / $shipping * 100, 0 );

		// only 0%, 7% and 19% are supported
		if ( ! isset( $allowed_tax_rates[ floatval( $shipping_rate ) ] ) ) {
			if ( $show_errors ) {
				echo sprintf( __( '<b>ERROR:</b> Unsupported tax rate: %s.', 'woocommerce-german-market' ), $shipping_rate . '%' );
				exit();
			} else {
				return;
			}
		}
		
		$voucher_items[] = array(
			'amount'			=> abs( $shipping_gross ),
			'taxAmount'			=> abs( $shipping_tax ),
			'taxRatePercent'	=> $shipping_rate,
			'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_shipping', $categoryId, $order )
		);

		$item_sum_refunded += abs( $shipping_gross );
		$item_tax_refunded += abs( $shipping_tax );
		
	}

	///////////////////////////////////
	// Fees
	///////////////////////////////////
	$fees = $refund->get_fees();

	foreach ( $fees as $fee ) {
		$fee_name 	= $fee[ 'name' ];
		$fee_total	= $fee->get_total();
		$fee_tax 	= $fee->get_total_tax();
		$fee_gross 	= $fee_total + $fee_tax;

		if ( abs( $fee_gross ) > 0.0 ) {

			$fee_rate = round( $fee_tax / $fee_total * 100, 0 );

			// only 0%, 7% and 19% are supported
			if ( ! isset( $allowed_tax_rates[ floatval( $fee_rate ) ] ) ) {
				if ( $show_errors ) {
					echo sprintf( __( '<b>ERROR:</b> Unsupported tax rate: %s.', 'woocommerce-german-market' ), $fee_rate . '%' );
					exit();
				} else {
					return;
				}
			}

			$voucher_items[] = array(
				'amount'			=> abs( $fee_gross ),
				'taxAmount'			=> abs( $fee_tax ),
				'taxRatePercent'	=> $fee_rate,
				'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_fee', $categoryId, $order )
			);

			$item_sum_refunded += abs( $fee_gross );
			$item_tax_refunded += abs( $fee_tax );

		}

	}

	///////////////////////////////////
	// general refund item or rounding ocrrection
	///////////////////////////////////
	if ( $item_sum_refunded < abs( $complete_refund_amount ) ) {

		$amount_of_general_refund = ( abs( $complete_refund_amount ) - $item_sum_refunded ) * ( -1 );

		$voucher_items[] = array(
			'amount'			=> abs( $amount_of_general_refund ),
			'taxAmount'			=> 0,
			'taxRatePercent'	=> 0,
			'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_fee', $categoryId, $order )
		);

		$item_sum_refunded += abs( $amount_of_general_refund );

	}

	///////////////////////////////////
	/// rebuild voucher items, max. three vouchers, one for each tax rate
	///////////////////////////////////
	
	if ( apply_filters( 'lexoffice_rebuild_voucher_items', true ) ) {
	
		// init
		$voucher_items_rebuild_helper = apply_filters( 'lexoffice_woocomerce_api_voucher_items_rebuild',
			array(

				0.0 => array(
					'amount'			=> 0.0,
					'taxAmount'			=> 0.0,
					'taxRatePercent'	=> 0.0,
					'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_split', $categoryId, $order )
				),

				5.0 => array(
					'amount'			=> 0.0,
					'taxAmount'			=> 0.0,
					'taxRatePercent'	=> 5.0,
					'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_split', $categoryId, $order )
				),

				7.0 => array(
					'amount'			=> 0.0,
					'taxAmount'			=> 0.0,
					'taxRatePercent'	=> 7.0,
					'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_split', $categoryId, $order )
				),

				16.0 => array(
					'amount'			=> 0.0,
					'taxAmount'			=> 0.0,
					'taxRatePercent'	=> 16.0,
					'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_split', $categoryId, $order )
				),

				19.0 => array(
					'amount'			=> 0.0,
					'taxAmount'			=> 0.0,
					'taxRatePercent'	=> 19.0,
					'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_split', $categoryId, $order )
				)

			)
		);

		// rebuild
		foreach ( $voucher_items as $voucher_item ) {
			$voucher_items_rebuild_helper[ floatval( $voucher_item[ 'taxRatePercent' ] ) ][ 'amount' ] += $voucher_item[ 'amount' ];
			$voucher_items_rebuild_helper[ floatval( $voucher_item[ 'taxRatePercent' ] ) ][ 'taxAmount' ] += $voucher_item[ 'taxAmount' ];
		}

		// check if amount > 0
		$voucher_items_rebuild = array(); // rebuild
		$total_tax_amount = 0.0;
		$total_amount = 0.0;
		foreach ( $voucher_items_rebuild_helper as $voucher_item_rebuild_helper ) {
			
			$voucher_item_rebuild_helper[ 'taxAmount' ] = $voucher_item_rebuild_helper[ 'amount' ] / ( 100.0 + $voucher_item_rebuild_helper[ 'taxRatePercent' ] ) * $voucher_item_rebuild_helper[ 'taxRatePercent' ];
			$voucher_item_rebuild_helper[ 'taxAmount' ] = round( $voucher_item_rebuild_helper[ 'taxAmount' ], 2 );
			$voucher_item_rebuild_helper[ 'amount' ] = round( $voucher_item_rebuild_helper[ 'amount' ], 2 );

			$total_tax_amount += $voucher_item_rebuild_helper[ 'taxAmount' ];
			$total_amount += round( $voucher_item_rebuild_helper[ 'amount' ], 2 );

			if ( $voucher_item_rebuild_helper[ 'amount' ] > 0.0 ) {
				$voucher_items_rebuild[] = $voucher_item_rebuild_helper;
			}

		}

	} else {

		$voucher_items_rebuild = $voucher_items;
		$total_tax_amount = 0.0;
		$total_amount = 0.0;
		foreach ( $voucher_items_rebuild as $key => $voucher_item ) {
			$voucher_items_rebuild[ $key ][ 'taxAmount' ]	= round( $voucher_item[ 'taxAmount' ], 2 );
			$voucher_items_rebuild[ $key ][ 'amount' ]		= round( $voucher_item[ 'amount' ], 2 );
			$total_tax_amount += $voucher_items_rebuild[ $key ][ 'taxAmount' ];
			$total_amount += $voucher_items_rebuild[ $key ][ 'amount' ];
		}

	}

	// due date
	$due_date_days_after_order_date = 0; // init
	$payment_method_id = $order->get_payment_method();
	$gateways = WC()->payment_gateways()->payment_gateways();
	if ( isset( $gateways[ $payment_method_id ] ) ) {
		$gateway = $gateways[ $payment_method_id ];
		if ( isset( $gateway->settings[ 'lexoffice_due_date' ] ) ) {
			$due_date_days_after_order_date = intval( $gateway->settings[ 'lexoffice_due_date' ] );
		}
	}
	$due_date = clone $refund->get_date_created();
	$voucher_date = apply_filters( 'lexoffice_woocommerce_api_order_voucher_date', $due_date->format( 'Y-m-d' ), $refund );
	$due_date->add( new DateInterval( 'P' . $due_date_days_after_order_date .'D' ) ); // add days

	// build data
	$array = array(
		'type'					=> 'salescreditnote',
		'voucherNumber'			=> apply_filters( 'lexoffice_woocommerce_api_order_voucher_number', $refund->get_id(), $refund ),
		'voucherDate'			=> $voucher_date,
		'dueDate'				=> apply_filters( 'lexoffice_woocomerce_api_refund_due_date', $due_date->format( 'Y-m-d' ), $refund ),
		'totalGrossAmount'		=> round( $total_amount, 2),
		'totalTaxAmount'		=> $total_tax_amount,
		'taxType'				=> 'gross',
		'remark'				=> trim( sprintf( __( 'Refund #%s for Order #%s', 'woocommerce-german-market' ), $refund->get_id(), $order->get_order_number() ) . ' ' . $refund_reason ),
		'voucherItems'			=> $voucher_items_rebuild,
	);

	// an order with toal 0 and empty voucher_items cannot be send to lexoffice
	if ( $total_amount == 0.0 && empty( $voucher_items_rebuild ) ) {
		if ( is_admin() && wp_doing_ajax() ) {
			if ( $show_errors ) {
				echo sprintf( __( '<b>ERROR:</b> You cannot send an order to lexoffice that has a total of 0,00 %s', 'woocommerce-german-market' ), get_woocommerce_currency_symbol() );
				exit();
			} else {
				return;
			}
		} else {
			error_log( sprintf( __( '<b>ERROR:</b> You cannot send an order to lexoffice that has a total of 0,00 %s', 'woocommerce-german-market' ), get_woocommerce_currency_symbol() ) );
			return;
		}
	}
	
	// add user or collective contact to voucher 
	$array = lexoffice_woocommerce_api_add_user_to_voucher( $array, $user, $refund );

	// add invoice pdf
	if ( $file ) {
		$array[ 'voucherImages' ] = array( $file );
	}

	// filter
	$array = apply_filters( 'lexoffice_woocomerce_api_order_to_curlopt_postfields_array', $array, $order, $voucher_items_rebuild, $voucher_items );

	ini_set( 'serialize_precision', -1 );
	$json = json_encode( $array, JSON_PRETTY_PRINT );

	return $json;

}
/**
* Create Curlopt Postfields
*
* @param WC_ORDER $order
* @param String $file
* @return String (JSON formated)
*/
function lexoffice_woocomerce_api_order_to_curlopt_postfields( $order, $file = null, $show_errors = true ) {

	// init data
	$user = $order->get_user();
	if ( ! $user ) {
		$user_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
	} else {
		$user_name = $user->display_name;
	}

	// Check Currency, only EUR is supported
	$currency = $order->get_currency();
	if ( $currency != 'EUR' ) {
		if ( $show_errors ) {
			echo sprintf( __( '"%s" is not a supported currency.', 'woocommerce-german-market' ), $currency );
			exit();
		} else {
			return;
		}
	}

	$tax_total = 0.0;
	$voucher_items = array();

	// allowed tax rates, values are net sums of items
	$allowed_tax_rates = apply_filters( 'lexoffice_woocomerce_api_allowed_tax_rates',
		array(	0.0		=> 0.0,
				5.0		=> 0.0,
				7.0		=> 0.0,
				16.0	=> 0.0,
				19.0 	=> 0.0,
		)
	);

	$categoryId	 = get_option( 'woocommerce_de_kleinunternehmerregelung' ) == 'on' ? '7a1efa0e-6283-4cbf-9583-8e88d3ba5960': '8f8664a8-fd86-11e1-a21f-0800200c9a66';

	if ( get_option( 'woocommerce_de_lexoffice_contacts', 'collective_contact' ) == 'lexoffice_contacts' ) {
		if ( get_option( 'woocommerce_de_kleinunternehmerregelung', 'off' ) != 'on' ) {
			if ( function_exists( 'wcvat_woocommerce_order_details_status' ) ) {
				$tax_exempt_status = wcvat_woocommerce_order_details_status( $order );
				if ( $tax_exempt_status == 'tax_free_intracommunity_delivery' ) {
					
					if ( ! empty( $order->get_billing_company() ) ) {
						$categoryId = '9075a4e3-66de-4795-a016-3889feca0d20';
					}
					
					} else if ( $tax_exempt_status == 'tax_exempt_export_delivery' ) {
						if ( ! empty( $order->get_billing_company() ) || apply_filters( 'lexoffice_woocommerce_use_tax_exempt_export_use_customer_as_company', false ) ) {
						$categoryId = '93d24c20-ea84-424e-a731-5e1b78d1e6a9';
					}
				}
			}
		}
	}

	///////////////////////////////////
	// first check if there is any item free of taxes
	///////////////////////////////////
	$tax_free_items = false;
	foreach ( $order->get_items() as $item ) {
		$tax = floatval( $order->get_line_tax( $item, false ) );
		if ( ! ( $tax > 0.0 ) ) {
			$tax_free_items = true;
		}
	}

	///////////////////////////////////
	// add order items as voucher items
	///////////////////////////////////
	$items = $order->get_items();
	foreach ( $items as $item ) {

		$line_total = floatval( $order->get_line_total( $item, false, false ) );
		$line_tax_total = floatval( $order->get_line_tax( $item, false ) );
		
		if ( $line_total > 0 ) {
			$tax_rate = round( ( $line_tax_total / ( $line_total ) ), 2 ) * 100;
		} else {
			
			if ( $line_total != 0.0 ) {
				$tax_rate = round( ( $line_tax_total / ( $line_total ) ), 2 ) * 100;
			} else {
				$tax_rate = 0.0;
			}

		}

		if ( ! isset( $allowed_tax_rates[ floatval( $tax_rate ) ] ) ) {

			// Fix Problems with discounts and wrong tax_amount, eg:
			// WooCommerce says: Total Net: 0.04, Tax: 0.01 => 25% rate, nonsense.
			// try to find tax amount without discounts
			$line_total_net 	= floatval( $order->get_line_subtotal( $item, false, false ) );
			$line_total_gross 	= floatval( $order->get_line_subtotal( $item, true, false ) );

			if ( $line_total_net > 0 ) {
				
				$maybe_tax_rate 	= round( ( $line_total_gross/$line_total_net - 1), 2 ) * 100;
				$max_tax_rate_not_rounded = ( $line_total_gross/$line_total_net - 1 ) * 100;

				if ( isset( $allowed_tax_rates[ floatval( $maybe_tax_rate ) ] ) ) {
					$tax_rate = $maybe_tax_rate;
				}

			}

		}
		
		// fix problems with small amounts 1
		if ( ! isset( $allowed_tax_rates[ floatval( $tax_rate ) ] ) ) {
			$item_data = $item->get_taxes();
			if ( isset( $item_data[ 'subtotal' ] ) && is_array( $item_data[ 'subtotal' ] ) && count( $item_data[ 'subtotal' ] ) == 1 ) {
				
				if ( function_exists( 'array_key_first' ) ) {
					$tax_rate_id = array_key_first( $item_data[ 'subtotal' ] );
				} else {
					foreach ( $item_data[ 'subtotal' ] as $key => $value ) {
						$tax_rate_id = $key;
						break;
					}
				}

				$maybe_tax_rate = intval( str_replace( '%', '', WC_Tax::get_rate_percent( $tax_rate_id ) ) );
				if ( isset( $allowed_tax_rates[ floatval( $maybe_tax_rate ) ] ) ) {
					$tax_rate = $maybe_tax_rate;
				}
			}
		}
		
		// fix problems with small amounts 2
		if ( ! isset( $allowed_tax_rates[ floatval( $tax_rate ) ] ) ) {

			$order_taxes =  $order->get_taxes();
			foreach ( $order_taxes as $tax ) {
				$tax_rate_percent = intval(  WC_Tax::get_rate_percent( $tax->get_rate_id() ) );
				if ( abs( $tax_rate_percent - $max_tax_rate_not_rounded ) <  1.0 ) {
					$tax_rate = $tax_rate_percent;
				}
			}
		}

		if ( ! isset( $allowed_tax_rates[ floatval( $tax_rate ) ] ) ) {
			$item_data = $item->get_taxes();
			if ( isset( $item_data[ 'subtotal' ] ) && is_array( $item_data[ 'subtotal' ] ) ) {
				foreach ( $item_data[ 'subtotal' ] as $rate_key => $item_data_subtotal ) {
					if ( $item_data_subtotal > 0 ) {
						$maybe_tax_rate = intval( str_replace( '%', '', WC_Tax::get_rate_percent( $rate_key ) ) );
						if ( $maybe_tax_rate > 0 ) {
							if ( isset( $allowed_tax_rates[ floatval( $maybe_tax_rate ) ] ) ) {
								$tax_rate = $maybe_tax_rate;
								break;
							}
						}
					}
				}
			}
		}

		$tax_rate = apply_filters( 'woocommerce_de_lexoffice_tax_rate_before_check', $tax_rate, $item );

		// only 0%, 7% and 19% are supported
		if ( ! isset( $allowed_tax_rates[ floatval( $tax_rate ) ] ) ) {
			if ( $show_errors ) {
				echo sprintf( __( '<b>ERROR:</b> Unsupported tax rate: %s.', 'woocommerce-german-market' ), $tax_rate . '%' );
				exit();
			} else {
				return;
			} 
		}

		// add for split tax calculations later
		$allowed_tax_rates[ floatval( $tax_rate ) ] += $order->get_line_total( $item, false );

		// add tax to tax total
		$tax_total += $order->get_line_tax( $item );

		$voucher_items[] = array(
			'amount'			=> $order->get_line_total( $item, true ),
			'taxAmount'			=> $order->get_line_tax( $item ),
			'taxRatePercent'	=> $tax_rate,
			'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_item', $categoryId, $item, $order )
		);
	}

	// order total without fees and shipping
	$total_without_fees_and_shipping = array_sum( $allowed_tax_rates );

	///////////////////////////////////
	// add shipping as voucher items, regading split tax
	///////////////////////////////////
	$shippings = $order->get_items( 'shipping' );

	foreach ( $shippings as $shipping ) {

		$shipping_net_total = 0;
		$shipping_tax 		= $shipping->get_taxes();

		// check if there are no taxes
		if ( ! ( array_sum( $shipping_tax[ 'total' ] ) ) > 0.0 ) {

			$voucher_items[] = array(
				'amount'			=> $shipping->get_total(),
				'taxAmount'			=> 0.0,
				'taxRatePercent'	=> 0.0,
				'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_shipping', $categoryId, $order )
			);

			continue;
		}

		if ( apply_filters( 'woocommerce_de_lexoffice_use_split_tax_shiping_taxes', false ) ) {

			add_filter( 'gm_split_tax_rounding_precision', function( $precision ) {
				return 100;
			});

			$use_split_tax = get_option( WGM_Helper::get_wgm_option( 'wgm_use_split_tax' ), 'on' );

			if ( 'on' === $use_split_tax ) {
				

				$shipping_split_tax = WGM_Tax::calculate_split_rate( $order->get_total_shipping(), $order, FALSE, '', 'shipping', true, false );
				
				$shipping_tax[ 'total' ] = array();
				foreach ( $shipping_split_tax[ 'rates' ] as $key => $infos ) {
					$shipping_tax[ 'total' ][ $key ] = $infos[ 'sum' ];
				}

			}
		}
		
		$net_parts 	  			= array();
		$net_parts_not_rounded	= array();
		$tax_parts				= array();

		$biggest_amount_for_rounding_corrections_key 	= null;
		$biggest_amount_for_rounding_corrections_value 	= 0;

		$smallest_amount_for_rounding_corrections_key 	= null;
		$smallest_amount_for_rounding_corrections_value = 0;

		foreach ( $shipping_tax[ 'total' ] as $rate_id => $rate_amount ) {

			if ( empty( $rate_amount ) ) {
				continue;
			}

			$percent = str_replace( '%', '', WC_Tax::get_rate_percent( $rate_id ) );
			$percent = floatval( str_replace( ',', '.', $percent ) );
			
			$net_parts_not_rounded[ $percent ] 	= $rate_amount / $percent * 100;
			$net_parts[ $percent ]				= round( $net_parts_not_rounded[ $percent ], 2 );
			$tax_parts[ $percent ]				= $rate_amount;

			// maybe we have to do a rounding correction
			if ( $rate_amount >= $biggest_amount_for_rounding_corrections_value ) {
				$biggest_amount_for_rounding_corrections_value = $rate_amount;
				$biggest_amount_for_rounding_corrections_key   = $percent;
			}

			if ( ! $smallest_amount_for_rounding_corrections_key ) {
				
				$smallest_amount_for_rounding_corrections_value = $rate_amount;
				$smallest_amount_for_rounding_corrections_key 	= $percent;

			} else {
				
				if ( $rate_amount <= $smallest_amount_for_rounding_corrections_value ) {
					$smallest_amount_for_rounding_corrections_value = $rate_amount;
					$smallest_amount_for_rounding_corrections_key 	= $percent;
				}

			}

		}

		$sum_of_nets = array_sum( $net_parts );
		$sum_of_nets_not_rounded = round( array_sum( $net_parts_not_rounded ), 2 );

		// correction if there is just one tax rate and percent calculation did wrong rounding
		if ( count( $net_parts_not_rounded ) == 1 ) {

			foreach ( $net_parts_not_rounded as $key => $value ) {
				$net_parts_not_rounded[ $key ] = $shipping->get_total();
				$net_parts[ $key ] = $shipping->get_total();
			}

			$sum_of_nets = array_sum( $net_parts );
			$sum_of_nets_not_rounded = round( array_sum( $net_parts_not_rounded ), 2 );

		}

		// do we have a shipping part free of taxes?
		if ( $tax_free_items ) {
			
			if ( floatval( $shipping->get_total() ) != $sum_of_nets_not_rounded ) {

				$last_shipping_part = $shipping->get_total() - $sum_of_nets_not_rounded;
				$net_parts_not_rounded[ 0 ] = $last_shipping_part;
				$net_parts[ 0 ]				= round( $net_parts_not_rounded[ 0 ], 2 );
				$tax_parts[ 0 ]				= 0.0;

				if ( $last_shipping_part >= $biggest_amount_for_rounding_corrections_value ) {
					$biggest_amount_for_rounding_corrections_value = $last_shipping_part;
					$biggest_amount_for_rounding_corrections_key   = 0;
				}

				$sum_of_nets = array_sum( $net_parts );
				$sum_of_nets_not_rounded = round( array_sum( $net_parts_not_rounded ), 2 );

			}

		}

		// maybe we have to do a rounding correction in some of the parts
		if ( $sum_of_nets != floatval( $shipping->get_total() ) ) {

			$diff = round( floatval( $shipping->get_total() ) - $sum_of_nets, 2 );
				
			if ( $smallest_amount_for_rounding_corrections_key ) {
				$net_parts[ $smallest_amount_for_rounding_corrections_key ] += $diff;
			}

		}

		foreach ( $net_parts as $percent => $amount ) {

			$voucher_items[] = array(
				'amount'			=> round( $amount + $tax_parts[ $percent ], 2 ),
				'taxAmount'			=> round( $tax_parts[ $percent ], 2 ),
				'taxRatePercent'	=> floatval( $percent ),
				'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_shipping', $categoryId, $order )
			);

		}

	}

	///////////////////////////////////
	// add fees as voucher items, regading split tax
	///////////////////////////////////
	$fees = $order->get_items( 'fee' );

	foreach ( $fees as $fee ) {

		$fee_net_total  = 0;
		$fee_tax 		= $fee->get_taxes();

		// check if there are no taxes
		if ( ! ( array_sum( $fee_tax[ 'total' ] ) ) > 0.0 ) {

			$voucher_items[] = array(
				'amount'			=> round( $fee->get_total(), 2 ),
				'taxAmount'			=> 0.0,
				'taxRatePercent'	=> 0.0,
				'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_shipping', $categoryId, $order )
			);

			continue;
		}
		
		$net_parts 	  			= array();
		$net_parts_not_rounded	= array();
		$tax_parts				= array();

		$biggest_amount_for_rounding_corrections_key 	= null;
		$biggest_amount_for_rounding_corrections_value 	= 0;

		$smallest_amount_for_rounding_corrections_key 	= null;
		$smallest_amount_for_rounding_corrections_value = 0;

		foreach ( $fee_tax[ 'total' ] as $rate_id => $rate_amount ) {

			if ( empty( $rate_amount ) ) {
				continue;
			}

			$percent = str_replace( '%', '', WC_Tax::get_rate_percent( $rate_id ) );
			$percent = floatval( str_replace( ',', '.', $percent ) );
			
			$net_parts_not_rounded[ $percent ] 	= $rate_amount / $percent * 100;
			$net_parts[ $percent ]				= round( $net_parts_not_rounded[ $percent ], 2 );
			$tax_parts[ $percent ]				= $rate_amount;

			// maybe we have to do a rounding correction
			if ( $rate_amount >= $biggest_amount_for_rounding_corrections_value ) {
				$biggest_amount_for_rounding_corrections_value = $rate_amount;
				$biggest_amount_for_rounding_corrections_key   = $percent;
			}

			if ( ! $smallest_amount_for_rounding_corrections_key ) {
				
				$smallest_amount_for_rounding_corrections_value = $rate_amount;
				$smallest_amount_for_rounding_corrections_key 	= $percent;

			} else {
				
				if ( $rate_amount <= $smallest_amount_for_rounding_corrections_value ) {
					$smallest_amount_for_rounding_corrections_value = $rate_amount;
					$smallest_amount_for_rounding_corrections_key 	= $percent;
				}

			}

		}

		$sum_of_nets = array_sum( $net_parts );
		$sum_of_nets_not_rounded = round( array_sum( $net_parts_not_rounded ), 2 );

		// correction if there is just one tax rate and percent calculation did wrong rounding
		if ( count( $net_parts_not_rounded ) == 1 ) {

			foreach ( $net_parts_not_rounded as $key => $value ) {
				$net_parts_not_rounded[ $key ] = $fee->get_total();
				$net_parts[ $key ] = $fee->get_total();
			}

			$sum_of_nets = array_sum( $net_parts );
			$sum_of_nets_not_rounded = round( array_sum( $net_parts_not_rounded ), 2 );

		}
		
		// do we have a fee part free of taxes?
		if ( $tax_free_items ) {
			if ( floatval( $fee->get_total() ) != $sum_of_nets_not_rounded ) {

				$last_fee_part 				= $fee->get_total() - $sum_of_nets_not_rounded;
				$net_parts_not_rounded[ 0 ] = $last_fee_part;
				$net_parts[ 0 ]				= round( $net_parts_not_rounded[ 0 ], 2 );
				$tax_parts[ 0 ]				= 0.0;

				if ( $last_fee_part >= $biggest_amount_for_rounding_corrections_value ) {
					$biggest_amount_for_rounding_corrections_value = $last_fee_part;
					$biggest_amount_for_rounding_corrections_key   = 0;
				}

				$sum_of_nets = array_sum( $net_parts );
				$sum_of_nets_not_rounded = round( array_sum( $net_parts_not_rounded ), 2 );

			}
		}

		// maybe we have to do a rounding correction in some of the parts
		if ( $sum_of_nets != floatval( $fee->get_total() ) ) {

			$diff = round( floatval( $fee->get_total() ) - $sum_of_nets, 2 );
				
				if ( $smallest_amount_for_rounding_corrections_key ) {
					$net_parts[ $smallest_amount_for_rounding_corrections_key ] += $diff;
				}

		}

		foreach ( $net_parts as $percent => $amount ) {

			$voucher_items[] = array(
				'amount'			=> round( $amount + $tax_parts[ $percent ], 2 ),
				'taxAmount'			=> $tax_parts[ $percent ],
				'taxRatePercent'	=> floatval( $percent ),
				'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_fees', $categoryId, $order )
			);

		}

	}

	///////////////////////////////////
	/// rebuild voucher items, max. three vouchers, one for each tax rate
	///////////////////////////////////
	
	// correction for items < 0 
	// @since GM 3.6.3
	foreach ( $voucher_items as $key => $voucher_item ) {
		
		if ( $voucher_item[ 'taxRatePercent' ] == 0 ) {

			if ( $voucher_item[ 'amount' ] > 0.0 ) {
				
				$test_rate_percent = round( $voucher_item[ 'taxAmount' ] / ( $voucher_item[ 'amount' ] - $voucher_item[ 'taxAmount' ] ) * 100 );
			
				if ( in_array( $test_rate_percent, array( 0.0, 5.0, 7.0, 16.0, 19.0 ) ) ) {
					$voucher_items[ $key ][ 'taxRatePercent' ] = $test_rate_percent;
				}

			}
			
		}
			
	}
	
	if ( apply_filters( 'lexoffice_rebuild_voucher_items', true ) ) {
	
		// init
		$voucher_items_rebuild_helper = apply_filters( 'lexoffice_woocomerce_api_voucher_items_rebuild',
			array(

				0.0 => array(
					'amount'			=> 0.0,
					'taxAmount'			=> 0.0,
					'taxRatePercent'	=> 0.0,
					'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_split', $categoryId, $order )
				),

				5.0 => array(
					'amount'			=> 0.0,
					'taxAmount'			=> 0.0,
					'taxRatePercent'	=> 5.0,
					'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_split', $categoryId, $order )
				),

				7.0 => array(
					'amount'			=> 0.0,
					'taxAmount'			=> 0.0,
					'taxRatePercent'	=> 7.0,
					'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_split', $categoryId, $order )
				),

				16.0 => array(
					'amount'			=> 0.0,
					'taxAmount'			=> 0.0,
					'taxRatePercent'	=> 16.0,
					'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_split', $categoryId, $order )
				),

				19.0 => array(
					'amount'			=> 0.0,
					'taxAmount'			=> 0.0,
					'taxRatePercent'	=> 19.0,
					'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_split', $categoryId, $order )
				)

			)
		);

		// rebuild
		foreach ( $voucher_items as $voucher_item ) {
			$voucher_items_rebuild_helper[ floatval( $voucher_item[ 'taxRatePercent' ] ) ][ 'amount' ] += $voucher_item[ 'amount' ];
			$voucher_items_rebuild_helper[ floatval( $voucher_item[ 'taxRatePercent' ] ) ][ 'taxAmount' ] += $voucher_item[ 'taxAmount' ];
		}

		// check if amount > 0
		$voucher_items_rebuild = array(); // rebuild
		$total_tax_amount = 0.0;
		$total_amount = 0.0;
		foreach ( $voucher_items_rebuild_helper as $voucher_item_rebuild_helper ) {
			
			$voucher_item_rebuild_helper[ 'taxAmount' ] = $voucher_item_rebuild_helper[ 'amount' ] / ( 100.0 + $voucher_item_rebuild_helper[ 'taxRatePercent' ] ) * $voucher_item_rebuild_helper[ 'taxRatePercent' ];
			$voucher_item_rebuild_helper[ 'taxAmount' ] = round( $voucher_item_rebuild_helper[ 'taxAmount' ], 2 );
			$voucher_item_rebuild_helper[ 'amount' ] = round( $voucher_item_rebuild_helper[ 'amount' ], 2 );

			$total_tax_amount += $voucher_item_rebuild_helper[ 'taxAmount' ];
			$total_amount += round( $voucher_item_rebuild_helper[ 'amount' ], 2 );

			if ( $voucher_item_rebuild_helper[ 'amount' ] > 0.0 ) {
				$voucher_items_rebuild[] = $voucher_item_rebuild_helper;
			}

		}

	} else {

		$voucher_items_rebuild = $voucher_items;
		$total_tax_amount = 0.0;
		$total_amount = 0.0;
		foreach ( $voucher_items_rebuild as $key => $voucher_item ) {
			$voucher_items_rebuild[ $key ][ 'taxAmount' ]	= round( $voucher_item[ 'taxAmount' ], 2 );
			$voucher_items_rebuild[ $key ][ 'amount' ]		= round( $voucher_item[ 'amount' ], 2 );
			$total_tax_amount += $voucher_items_rebuild[ $key ][ 'taxAmount' ];
			$total_amount += $voucher_items_rebuild[ $key ][ 'amount' ];
		}

	}

	///////////////////////////////////
	// rounding error handling
	///////////////////////////////////
	if ( round( $total_amount, wc_get_price_decimals() ) != round( $order->get_total(), wc_get_price_decimals() ) ) {
		$difference = round( $order->get_total(), wc_get_price_decimals() ) - round( $total_amount, wc_get_price_decimals() );
		$difference = round( $difference, wc_get_price_decimals() );

		if ( $difference > 0.0 ) {

			$voucher_items_rebuild[] = array(
					'amount'			=>	$difference,
				    'taxAmount'			=>	0.0,
				    'taxRatePercent'	=>	0.0,
				    'categoryId'		=> 'aba9020f-d0a6-47ca-ace6-03d6ed492351'
			);

			$total_amount = round( $order->get_total(), wc_get_price_decimals() );

		}
		
	}

	///////////////////////////////////
	// build array for order
	///////////////////////////////////

	// due date
	$due_date_meta_data = get_post_meta( $order->get_id(), '_wgm_due_date', true );

	if ( $due_date_meta_data == '' ) {

		$due_date_days_after_order_date = 0; // init
		$payment_method_id = $order->get_payment_method();
		$gateways = WC()->payment_gateways()->payment_gateways();
		if ( isset( $gateways[ $payment_method_id ] ) ) {
			$gateway = $gateways[ $payment_method_id ];
			if ( isset( $gateway->settings[ 'lexoffice_due_date' ] ) ) {
				$due_date_days_after_order_date = intval( $gateway->settings[ 'lexoffice_due_date' ] );
			} else {
				
				$current_payment_gateway = $gateway->id;

				if ( $current_payment_gateway == 'bacs' ) {
					$due_date_days_after_order_date = 10;
				} else if ( $current_payment_gateway == 'cheque' ) {
					$due_date_days_after_order_date = 14;
				} else if ( $current_payment_gateway == 'paypal' ) {
					$due_date_days_after_order_date = 0;
				} else if ( $current_payment_gateway == 'cash_on_delivery' ) {
					$due_date_days_after_order_date = 7;
				} else if ( $current_payment_gateway == 'german_market_purchase_on_account' ) {
					$due_date_days_after_order_date = 30;
				} else {
					$due_date_days_after_order_date = 0;
				}
			}
		}

		$due_date = clone $order->get_date_created();
		$voucher_date = apply_filters( 'lexoffice_woocommerce_api_order_voucher_date', $due_date->format( 'Y-m-d' ), $order );
		$due_date = new DateTime( $voucher_date );
		$due_date->add( new DateInterval( 'P' . $due_date_days_after_order_date .'D' ) ); // add days
		$due_date_meta_data = $due_date->format( 'Y-m-d' );

	} else {

		// due date is set as meta
		$date_created = clone $order->get_date_created();
		$voucher_date = apply_filters( 'lexoffice_woocommerce_api_order_voucher_date', $date_created->format( 'Y-m-d' ), $order );
	}

	// build data
	$array = array(
		'type'					=> 'salesinvoice',
		'voucherNumber'			=> apply_filters( 'lexoffice_woocommerce_api_order_voucher_number', $order->get_order_number(), $order ),
		'voucherDate'			=> $voucher_date,
		'dueDate'				=> apply_filters( 'lexoffice_woocomerce_api_order_due_date', $due_date_meta_data, $order ),
		'totalGrossAmount'		=> round( $total_amount, 2 ),
		'totalTaxAmount'		=> round( $total_tax_amount, 2 ),
		'taxType'				=> 'gross',
		'remark'				=> sprintf( __( 'Order from %s', 'woocommerce-german-market' ), $user_name ),
		'voucherItems'			=> $voucher_items_rebuild,
	);

	// an order with toal 0 and empty voucher_items cannot be send to lexoffice
	if ( $total_amount == 0.0 && empty( $voucher_items_rebuild ) ) {
		if ( is_admin() && wp_doing_ajax() ) {
			if ( $show_errors ) {
				echo sprintf( __( '<b>ERROR:</b> You cannot send an order to lexoffice that has a total of 0,00 %s', 'woocommerce-german-market' ), get_woocommerce_currency_symbol() );
				exit();
			} else {
				return;
			}
		} else {
			error_log( sprintf( __( '<b>ERROR:</b> You cannot send an order to lexoffice that has a total of 0,00 %s', 'woocommerce-german-market' ), get_woocommerce_currency_symbol() ) );
			return;
		}
	}

	// add user or collective contact to voucher 
	$array = lexoffice_woocommerce_api_add_user_to_voucher( $array, $user, $order );

	// add invoice pdf
	if ( $file ) {
		$array[ 'voucherImages' ] = array( $file );
	}

	// filter
	$array = apply_filters( 'lexoffice_woocomerce_api_order_to_curlopt_postfields_array', $array, $order, $voucher_items_rebuild, $voucher_items );

	ini_set( 'serialize_precision', -1 );
	$json = json_encode( $array, JSON_PRETTY_PRINT );

	return $json;
}

/**
* API - send invoice pdf
*
* @param WC_ORDER $order
* @return String json response
*/
function lexoffice_woocomerce_api_upload_invoice_pdf( $voucher_id, $order, $is_refund = false, $show_errors = true ) {

	if ( ! class_exists( 'WP_WC_Invoice_Pdf_Create_Pdf' ) ) {
		if ( $show_errors ) {
			echo __( '<b>ERROR:</b> Modul Invoice PDF of WooCommerce German Market is not enabled.', 'woocommerce-german-market' );
			exit();
		} else {
			return;
		}
	}

	if ( $is_refund ) {

		$refund 	= $order;
		$refund_id 	= $refund->get_id();
		$order_id 	= $refund->get_parent_id();
		$order 		= wc_get_order( $order_id );

		WGM_Compatibilities::wpml_invoice_pdf_switch_lang_for_online_booking( array( 'order' => $order, 'admin' => 'true' ) );

		do_action( 'wp_wc_invoice_pdf_before_refund_backend_download', $refund_id );

		add_filter( 'wp_wc_invoice_pdf_template_invoice_content', array( 'WP_WC_Invoice_Pdf_Backend_Download', 'load_storno_template' ) );

		// get filename
		$filename = get_option( 'wp_wc_invoice_pdf_refund_file_name_backend', 'Refund-{{refund-id}} for order {{order-number}}' );
		// replace {{refund-id}}, the other placeholders will be managed by the class WP_WC_Invoice_Pdf_Create_Pdf
		$filename = str_replace( '{{refund-id}}', $refund_id, $filename );
		$filename = apply_filters( 'wp_wc_invoice_pdf_refund_backend_filename', $filename, $refund );

		$args = array( 
					'order'				=> $order,
					'refund'			=> $refund,
					'output_format'		=> 'pdf',
					'output'			=> 'cache',
					'filename'			=> str_replace( '/', '-', $filename ),
					'admin'				=> 'true',
				);

	} else {

		WGM_Compatibilities::wpml_invoice_pdf_switch_lang_for_online_booking( array( 'order' => $order, 'admin' => 'true' ) );

		$args = array( 
			'order'				=> $order,
			'output_format'		=> 'pdf',
			'output'			=> 'cache',
			'filename'			=> str_replace( '/', '-', apply_filters( 'wp_wc_invoice_pdf_frontend_filename', get_option( 'wp_wc_invoice_pdf_file_name_frontend', get_bloginfo( 'name' ) . '-' . __( 'Invoice-{{order-number}}', 'woocommerce-invoice-pdf' ) ), $order ) ),
			'admin'				=> 'true',
		);

	}
	
		
	$invoice 	= new WP_WC_Invoice_Pdf_Create_Pdf( $args );
  	$attachment = WP_WC_INVOICE_PDF_CACHE_DIR . $invoice->cache_dir . DIRECTORY_SEPARATOR . $invoice->filename;

  	WGM_Compatibilities::wpml_invoice_pdf_reswitch_lang_for_online_booking();
  	
  	if ( $is_refund ) {
  		remove_filter( 'wp_wc_invoice_pdf_template_invoice_content', array( 'WP_WC_Invoice_Pdf_Backend_Download', 'load_storno_template' ) );
  	}

  	///////////////////////////////////
	// 1st step: upload post
	///////////////////////////////////

  	// create CURLFile
	$cfile = new CURLFile( $attachment );

	$post = array (
	    'file' => $cfile,
	    'type' => 'voucher'
	);

	$curl = curl_init();

	curl_setopt_array( $curl, array(
	  CURLOPT_URL => "https://api.lexoffice.io/v1/vouchers/" . $voucher_id . "/files",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_POSTFIELDS => $post,
	  CURLOPT_HTTPHEADER => array(
	    "accept: application/json",
	    "authorization: Bearer " . lexoffice_woocomerce_api_get_bearer(),
	    "cache-control: no-cache",
	  ),
	) );

	                                                                                                                                                                                                                
	$response_post = curl_exec( $curl );
	curl_close( $curl );

	// evaluate response
	$response_array = json_decode( $response_post, true );
	if ( ! isset( $response_array[ 'id' ] ) ) {
		if ( $show_errors ) {
			echo '<b>' . __( 'ERROR', 'woocommerce-german-market' ) . ':</b> ' . lexoffice_woocomerce_get_error_text( $response_post );
			exit();
		} else {
			return;
		}
		
	}

	return $response_post;

}

/**
* Get voucher status
*
* @param String $voucher_id
* @param $return_bool
* @return Boolean (true if voucher exists) | Array if $return_bool is set to false
*/
function lexoffice_woocommerce_api_get_vouchers_status( $voucher_id, $return_bool = true) {

	if ( $voucher_id == '' ) {
		return true;
	}

	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "https://api.lexoffice.io/v1/vouchers/" . $voucher_id ,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "GET",
	  CURLOPT_HTTPHEADER => array(
	    "accept: application/json",
	    "authorization: Bearer ". lexoffice_woocomerce_api_get_bearer(),
	    "cache-control: no-cache"
	  ),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);
	
	$response_array = json_decode( $response, true );

	if ( ! $return_bool ) {
		return $response_array;
	}

	// if there is no connection, pretend voucher is still available
	if ( isset( $response_array[ 'error' ] ) && $response_array[ 'error' ] == 'Not Found' || $response == '' ) {
		return false;
	}

	return true;
}

/**
* API - get auth bearer, OAuth2 authorization
* @return String
*/
function lexoffice_woocomerce_api_get_bearer() {

	$bearer 		= get_option( 'lexoffice_woocommerce_barear', '' );
	$code 			= get_option( 'woocommerce_de_lexoffice_authorization_code', '' );
	$last_used_code = get_option( 'lexoffice_woocommerce_last_auth_code', '' );

	// reconnect
	if ( $code != $last_used_code ) {
		delete_option( 'lexoffice_woocommerce_barear' );
		delete_option( 'lexoffice_woocommerce_refresh_token' );
		delete_option( 'lexoffice_woocommerce_refresh_time' );
		delete_option( 'lexoffice_woocommerce_last_auth_code' );
	} 
	
	///////////////////////////////////
	// if barear is empty => OAuth2
	///////////////////////////////////

	if ( $bearer == '' ) {
		
		// if code is empty => exit
		if ( $code == '' ) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				echo __( '<b>ERROR:</b> There is not authorization code. Please go to the WooCommerce German Market settings and enter a valid authorization code.', 'woocommerce-german-market' );
				exit();
			} else {
				return '';
			}
		}

		// get bearer
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://app.lexoffice.de/api/oauth2/token?grant_type=authorization_code&code=" . $code . "&redirect_uri=%2Fapi%2Foauth2%2Fauthorization_code",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_HTTPHEADER => array(
		    "accept: application/json",
		    "authorization: Basic ZGUxNmFkNzgtOWM4NC00ODc3LWJmMjUtMTQwMDVkODM3NDNhOjc3PVokQFlfW0d2d1UoUiE=",
		    "cache-control: no-cache",
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		
		curl_close($curl);
			
		$response_array = json_decode( $response, true );

		if ( isset( $response_array[ 'access_token' ] ) ) {

			// update bearer
			$bearer = $response_array[ 'access_token' ];
			update_option( 'lexoffice_woocommerce_barear', $bearer );

			// set refresh token
			update_option( 'lexoffice_woocommerce_refresh_token', $response_array[ 'refresh_token' ] );

			// set refresh time
			$refresh_time = time() + intval( $response_array[ 'expires_in' ] );
			update_option( 'lexoffice_woocommerce_refresh_time', $refresh_time );

			// save used authorization code
			update_option( 'lexoffice_woocommerce_last_auth_code', $code );

		}

	}

	///////////////////////////////////
	// Do we need to refresh the bearer?
	///////////////////////////////////
	$refesh_time = intval( get_option( 'lexoffice_woocommerce_refresh_time' ) );
	
	if ( $refesh_time > 0 ) {

		// we need a new one
		if ( $refesh_time - 100 - time() < 0 ) {

			$refresh_token = get_option( 'lexoffice_woocommerce_refresh_token' );

			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => "https://app.lexoffice.de/api/oauth2/token?grant_type=refresh_token&refresh_token=" . $refresh_token . "&redirect_uri=%2Fapi%2Foauth2%2Fauthorization_code",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_HTTPHEADER => array(
			    "accept: application/json",
			    "authorization: Basic ZGUxNmFkNzgtOWM4NC00ODc3LWJmMjUtMTQwMDVkODM3NDNhOjc3PVokQFlfW0d2d1UoUiE=",
			    "cache-control: no-cache",
			  ),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);
			
			$response_array = json_decode( $response, true );

			if ( isset( $response_array[ 'access_token' ] ) ) {

				// update bearer
				$bearer = $response_array[ 'access_token' ];
				update_option( 'lexoffice_woocommerce_barear', $bearer );

				// set refresh token
				update_option( 'lexoffice_woocommerce_refresh_token', $response_array[ 'refresh_token' ] );

				// set refresh time
				$refresh_time = time() + intval( $response_array[ 'expires_in' ] );
				update_option( 'lexoffice_woocommerce_refresh_time', $refresh_time );

			}

		}

	}

	return $bearer;

}

/**
* Revoke Authorization
*/
function lexoffice_woocomerce_api_revoke_auth() {

	$curl = curl_init();

	curl_setopt_array( $curl, 

		array(
		  	CURLOPT_URL => "https://api.lexoffice.io/v1/revoke",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_HTTPHEADER => array(
			    "accept: application/json",
			    "authorization: Bearer " . lexoffice_woocomerce_api_get_bearer(),
			    "cache-control: no-cache",
			    "content-type: application/json",
			  ),
		)

	);

	$response = curl_exec( $curl );
	$response_array = json_decode( $response, true );
	curl_close( $curl );

}

/**
* Get beauty error text from json string if possible
* @param String
* @return String
*/
function lexoffice_woocomerce_get_error_text( $json ) {

	// init
	$return = $json;

	$array = json_decode( $json, true );
	if ( isset( $array[ 'error_description' ] ) ) {
		$return = $array[ 'error_description' ];
	}

	return apply_filters( 'lexoffice_woocommerce_error_message', $return, $json );

}

/**
* Get all contacts
* @return Array
*/
function lexoffice_woocommerce_get_all_contacts() {

	if ( get_option( 'woocommerce_de_lexoffice_too_many_contacts', 'no' ) == 'yes' ) {
		if ( apply_filters( 'woocommerce_de_lexoffice_too_many_contacts', true ) ) {
			return array();
		}
	}

	$curl = curl_init();

	curl_setopt_array( $curl, 

		array(
		  	CURLOPT_URL => "https://api.lexoffice.io/v1/contacts?size=100",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
			    "accept: application/json",
			    "authorization: Bearer " . lexoffice_woocomerce_api_get_bearer(),
			    "cache-control: no-cache",
			    "content-type: application/json",
			  ),
		)
	);

	$response 		= curl_exec( $curl );
	$response_array = json_decode( $response, true );

	curl_close( $curl );

	// simple error handling
	if ( ! isset( $response_array[ 'content' ] ) ) {
		return array();
	}

	$contacts 		= $response_array[ 'content' ];
	$total_pages 	= $response_array[ 'totalPages' ];

	if ( $total_pages > 10 ) {
		update_option( 'woocommerce_de_lexoffice_too_many_contacts', 'yes' );
		return array();
	}

	if ( $total_pages > 1 ) {

		for ( $i = 2; $i<= $total_pages; $i++ ) {

			$page = $i - 1;

			$curl = curl_init();

			curl_setopt_array( $curl, 

				array(
				  	CURLOPT_URL => "https://api.lexoffice.io/v1/contacts/?page=" . $page . "&size=100",
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => "",
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 30,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => "GET",
					CURLOPT_HTTPHEADER => array(
					    "accept: application/json",
					    "authorization: Bearer " . lexoffice_woocomerce_api_get_bearer(),
					    "cache-control: no-cache",
					    "content-type: application/json",
					  ),
				)
			);

			$response 		= curl_exec( $curl );
			$response_array = json_decode( $response, true );
			curl_close( $curl );

			$contacts = array_merge( $contacts, $response_array[ 'content' ] );

		}

	}

	return $contacts;

}

/**
* Create a new lexoffice user
* @param WP_USer $wp_user
* @param WC_Order $order
* @return String (lexoffice contact id)
*/
function lexoffice_woocommerce_create_new_user( $wp_user, $order = null ) {

	$array = lexoffice_woocommerce_build_customer_array( $wp_user, $order );
	$json = json_encode( $array, JSON_PRETTY_PRINT );
	$curl = curl_init();

	curl_setopt_array( $curl, 

		array(
		  	CURLOPT_URL => "https://api.lexoffice.io/v1/contacts/",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $json,
			CURLOPT_HTTPHEADER => array(
			    "accept: application/json",
			    "authorization: Bearer " . lexoffice_woocomerce_api_get_bearer(),
			    "cache-control: no-cache",
			    "content-type: application/json",
			  ),
		)

	);

	$response = curl_exec( $curl );
	$response_array = json_decode( $response, true );
	curl_close( $curl );

	if ( isset( $response_array[ 'id' ] ) ) {
		return $response_array[ 'id' ];
	} else {
		echo __( 'ERROR: Could not create new lexoffice user', 'woocommerce-german-market' );
	}
}

/**
* Build array for wp_user to be send to lexoffice
* @param WP_User $wp_user
* @param WP_Order $order
* @return array
*/
function lexoffice_woocommerce_build_customer_array( $wp_user, $order = null, $lexoffice_user_data = null ) {

	$customer 			= array();
	$role_customer 		= new stdClass();
	$person 			= new stdClass();
	$company 			= new stdClass();
	$billing_address	= new stdClass();
	$shipping_address 	= new stdClass();
	$addresses 			= array();

	$is_company 		= false;
	$billing_address_is_empty = true;
	$shipping_address_is_empty = true;

	$address_meta_mapping = array(
		'address_1'		=> 'street',
		'address_2'		=> 'supplement',
		'postcode'		=> 'zip',
		'city'			=> 'city',
		'country'		=> 'countryCode',
	);

	$order_prefix = $order ? '_' : '';
	$addresses_pre = array(
		$order_prefix . 'billing_',
		$order_prefix . 'shipping_'
	);

	$person->salutation = apply_filters( 'lexoffice_woocommerce_create_new_user_default_salutation', 'Herr', $wp_user, $order );

	$order_get_address = $order;
	if ( $order_get_address && $order_get_address->get_type() == 'shop_order_refund' ) {
		$order_get_address = wc_get_order( $order->get_parent_id() );
	}

	if ( $order ) {
		$person->lastName 		= $order_get_address->get_billing_last_name();
		$email 					= $order_get_address->get_billing_email();
		$first_name 			= $order_get_address->get_billing_first_name();
		$company_name 			= $order_get_address->get_billing_company();
		$phone 					= $order_get_address->get_billing_phone();
	} else {
		$person->lastName 		= get_user_meta( $wp_user->ID, 'billing_last_name', true );
		$email 					= get_user_meta( $wp_user->ID, 'billing_email', true );
		$first_name 			= get_user_meta( $wp_user->ID, 'billing_first_name', true );
		$company_name 			= get_user_meta( $wp_user->ID, 'billing_company', true );
		$phone 					= get_user_meta( $wp_user->ID, 'billing_phone', true );
	}

	if ( $first_name != '' ) {
		$person->firstName = $first_name;
	}

	// init addresses
	foreach ( $addresses_pre as $pre ) {

		foreach ( $address_meta_mapping as $woocommerce_key => $lexoffice_key ) {

			if ( $order_get_address ) {
				
				$method_name = 'get' . $pre .  $woocommerce_key;
				if ( WGM_Helper::method_exists( $order_get_address, $method_name ) ) {
					$value = $order_get_address->$method_name();
				} else {
					$value = get_post_meta( $order_get_address->get_id(), $pre . $woocommerce_key, true );
				}

			} else {
				$value = get_user_meta( $wp_user->ID, $pre . $woocommerce_key, true );
			}
			
			if ( $value != '' ) {

				if ( $pre == 'billing_' || $pre == '_billing_' ) {
					$billing_address->$lexoffice_key = $value;
					$billing_address_is_empty = false;
				} else {
					$shipping_address->$lexoffice_key = $value;
					$shipping_address_is_empty = false;
				}

			}
		}

	}

	if ( apply_filters( 'lexoffice_woocommerce_use_tax_exempt_export_use_customer_as_company', false ) ) {
		
		if ( function_exists( 'wcvat_woocommerce_order_details_status' ) ) {
			if ( $order->get_type() == 'shop_order_refund' ) {
				$parent_order_of_refund = wc_get_order( $order->get_parent_id() );
				$tax_exempt_status = wcvat_woocommerce_order_details_status( $parent_order_of_refund );
			} else {
				$tax_exempt_status = wcvat_woocommerce_order_details_status( $order );
			}
			
			if ( $tax_exempt_status == 'tax_exempt_export_delivery' ) {
				$company_name = $person->lastName;
			}
		}

	}

	if ( $company_name != '' ) {
		$is_company = true;
	}

	if ( ! $is_company ) {

		$customer = array(
			'version' 	=> 0,
			'roles' 	=> array(
				'customer' => $role_customer
			),
			'person' => $person,
			'emailAddresses' => array( 'private' => array( $email ) )
		);

	} else {

		$company = new stdClass();
		$company->name = $company_name;

		if ( isset( $person->lastName ) ) {
			if ( ! empty( $person->lastName ) ) {
				$company->contactPersons = array( $person );
			}
		}		

		$billing_vat = $order->get_meta( 'billing_vat' );
		if ( ! empty( $billing_vat ) ) {
			$company->vatRegistrationId = $billing_vat;
		}

		$customer = array(
			'version' 	=> 0,
			'roles' 	=> array(
				'customer' => $role_customer
			),
			'company' => $company,
			'emailAddresses' => array( 'office' => array( $email ) )
		);

	}
	
	if ( ( ! $billing_address_is_empty ) ||  ( ! $shipping_address_is_empty ) ) {

		if ( ! $billing_address_is_empty ) {
			$addresses[ 'billing' ] = array( $billing_address );
		}

		if ( ! $shipping_address_is_empty ) {
			$addresses[ 'shipping' ] = array( $shipping_address );
		}

		$customer[ 'addresses' ] = $addresses;
	}

	if ( $phone != '' ) {
		$private_or_office = $is_company ? 'office' : 'private';
		$customer[ 'phoneNumbers' ] = array( $private_or_office => array( $phone ) );
	}

	if ( is_array( $lexoffice_user_data ) ) {
		if ( isset( $lexoffice_user_data[ 'note' ] ) ) {
			$customer[ 'note' ] = $lexoffice_user_data[ 'note' ];
		}
	}
	
	// filter
	return apply_filters( 'lexoffice_woocomerce_api_customer_array', $customer, $wp_user );

}

/**
* Use Collective Contact or lexoffice Users when sending the voucher
*
* @param Array $array
* @param WP_User $user
* @param WC_Order $order
* @return Array
**/
function lexoffice_woocommerce_api_add_user_to_voucher( $array, $user, $order = null ) {

	if ( get_option( 'woocommerce_de_lexoffice_contacts', 'collective_contact' ) == 'collective_contact' ) {

			$array[ 'useCollectiveContact' ] = true;
		
		} else {

			if ( $user && ( intval( $user->ID ) > 0 ) ) {

				// registered user
				$lexoffice_user_meta = get_user_meta( $user->ID, 'lexoffice_contact', true );
				if ( $lexoffice_user_meta == '' ) {
					$lexoffice_user_meta = '0';
				}
				
				if ( $lexoffice_user_meta != '0' ) {

					// a lexoffice user is already assigned to the woocommerce user
					// now test if the user still exists
					
					$still_exists = true;

					$curl = curl_init();
					curl_setopt_array( $curl, 

						array(
						  	CURLOPT_URL => "https://api.lexoffice.io/v1/contacts/" . $lexoffice_user_meta,
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_ENCODING => "",
							CURLOPT_MAXREDIRS => 10,
							CURLOPT_TIMEOUT => 30,
							CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
							CURLOPT_CUSTOMREQUEST => "GET",
							CURLOPT_HTTPHEADER => array(
							    "accept: application/json",
							    "authorization: Bearer " . lexoffice_woocomerce_api_get_bearer(),
							    "cache-control: no-cache",
							    "content-type: application/json",
							  ),
						)
					);

					$response 		= curl_exec( $curl );
					$response_array = json_decode( $response, true );

					curl_close( $curl );

					if ( ! isset( $response_array[ 'id' ] ) ) {
						$still_exists = false;
						update_user_meta( $user->ID, 'lexoffice_contact', '0' );
						$lexoffice_user_meta = '0';
					}

					if ( $still_exists ) {
						
						// user exists, so use this lexoffice user
						$array[ 'useCollectiveContact' ] = false;
						$array[ 'contactId' ] = $lexoffice_user_meta;

						if ( get_option( 'woocommerce_de_lexoffice_user_update', 'on' ) == 'on' ) {

							$user_array = lexoffice_woocommerce_build_customer_array( $user, $order, $response_array );
							$user_array[ 'version' ] = $response_array[ 'version' ];

							$json = json_encode( $user_array, JSON_PRETTY_PRINT );
							$curl = curl_init();

							curl_setopt_array( $curl, 

								array(
								  	CURLOPT_URL => "https://api.lexoffice.io/v1/contacts/" . $lexoffice_user_meta,
									CURLOPT_RETURNTRANSFER => true,
									CURLOPT_ENCODING => "",
									CURLOPT_MAXREDIRS => 10,
									CURLOPT_TIMEOUT => 30,
									CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
									CURLOPT_CUSTOMREQUEST => "PUT",
									CURLOPT_POSTFIELDS => $json,
									CURLOPT_HTTPHEADER => array(
									    "accept: application/json",
									    "authorization: Bearer " . lexoffice_woocomerce_api_get_bearer(),
									    "cache-control: no-cache",
									    "content-type: application/json",
									  ),
								)

							);

							$response = curl_exec( $curl );
							$response_array = json_decode( $response, true );
							curl_close( $curl );
						}

					} else {

						// maybe create new user
						if ( get_option( 'woocommerce_de_lexoffice_create_new_user', 'off' ) == 'on' ) {
							$lexoffice_user_meta = lexoffice_woocommerce_create_new_user( $user, $order );
							update_user_meta( $user->ID, 'lexoffice_contact', $lexoffice_user_meta );
							$array[ 'useCollectiveContact' ] = false;
							$array[ 'contactId' ] = $lexoffice_user_meta;
						} else {
							$array[ 'useCollectiveContact' ] = true;
						}

					}

				} else {

					// maybe create new user
					if ( get_option( 'woocommerce_de_lexoffice_create_new_user', 'off' ) == 'on' ) {
						$lexoffice_user_meta = lexoffice_woocommerce_create_new_user( $user, $order );
						update_user_meta( $user->ID, 'lexoffice_contact', $lexoffice_user_meta );
						$array[ 'useCollectiveContact' ] = false;
						$array[ 'contactId' ] = $lexoffice_user_meta;
					} else {
						$array[ 'useCollectiveContact' ] = true;
					}
				}
				
			} else {

				// guest user handling
				$guest_handling = get_option( 'woocommerce_de_lexoffice_guest_user', 'collective_contact' );
				if ( $guest_handling == 'collective_contact' ) {
					$array[ 'useCollectiveContact' ] = true;
				} else if ( $guest_handling == 'create_new_user' ) {
					
					$order_get_address = $order;
					if ( $order_get_address->get_type() == 'shop_order_refund' ) {
						$order_get_address = wc_get_order( $order->get_parent_id() );
					}

					$email = $order_get_address->get_billing_email();
					
					// search if user with this email exists
					$curl = curl_init();

					curl_setopt_array( $curl, 
						array(
						  	CURLOPT_URL => "https://api.lexoffice.io/v1/contacts/?email=" . $email,
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_ENCODING => "",
							CURLOPT_MAXREDIRS => 10,
							CURLOPT_TIMEOUT => 30,
							CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
							CURLOPT_CUSTOMREQUEST => "GET",
							CURLOPT_HTTPHEADER => array(
							    "accept: application/json",
							    "authorization: Bearer " . lexoffice_woocomerce_api_get_bearer(),
							    "cache-control: no-cache",
							    "content-type: application/json",
							  ),
						)
					);

					$response = curl_exec( $curl );
					$response_array = json_decode( $response, true );
					curl_close( $curl );

					$found_user = false;

					if ( isset( $response_array[ 'content' ] ) ) {
						
						foreach ( $response_array[ 'content' ] as $found_user ) {
							if ( isset( $found_user[ 'id' ] ) ) { // found user with this email
								$found_user = $found_user[ 'id' ]; 
								break;
							}
						}

					} 

					if ( $found_user ) {

						// use found user
						$array[ 'contactId' ] = $found_user;

					} else {

						// create new user
						$lexoffice_user_meta = lexoffice_woocommerce_create_new_user( $user, $order );
						$array[ 'contactId' ] = $lexoffice_user_meta;
					}

					$array[ 'useCollectiveContact' ] = false;

				} else {
					$array[ 'useCollectiveContact' ] = false;
					$array[ 'contactId' ] = $guest_handling;
				}

			}

		}

	return $array;

}
