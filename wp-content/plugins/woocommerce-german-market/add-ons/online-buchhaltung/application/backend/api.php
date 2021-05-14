<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
* API - send order
*
* @param WC_ORDER $order
* @return String ("SUCCESS" or "ERROR: {your error Message}")
*/
function online_buchhaltung_1und1_api_send_order( $order ) {

	// get all we need, may throws errors and exit
	$args = array(
		'api_token'		=> online_buchhaltung_1und1_api_get_api_token(),
		'base_url'		=> online_buchhaltung_1und1_api_get_base_url(),
		'order'			=> online_buchhaltung_1und1_api_check_order( $order ),
		'invoice_pdf'	=> online_buchhaltung_1und1_api_get_invoice_pdf( $order )
	);
	
	// build temp file, may throws an error and exits
	$args[ 'temp_file' ] = online_buchhaltung_1und1_api_build_temp_file( $args );

	// create customer or update user data
	$args[ 'customer' ] = online_buchhaltung_1und1_api_contact( $order->get_user_id(), $args );

	// send voucher to 1und1 online-buchhaltung
	$voucher_id = online_buchhaltung_1und1_api_send_voucher( $args );

	// save 1und1 online-buchhaltung id as post meta
	update_post_meta( $order->get_id(), '_online_buchhaltung_1und1_has_transmission', $voucher_id );

	return 'SUCCESS';

}

/**
* API - send refund
*
* @param WC_ORDER $order
* @return String ("SUCCESS" or "ERROR: {your error Message}")
*/
function online_buchhaltung_1und1_api_send_refund( $refund ) {

	// get all we need, may throws errors and exit
	$args = array(
		'api_token'		=> online_buchhaltung_1und1_api_get_api_token(),
		'base_url'		=> online_buchhaltung_1und1_api_get_base_url(),
		'refund'		=> online_buchhaltung_1und1_api_check_order( $refund ),
		'order'			=> wc_get_order( $refund->get_parent_id() ),
		'invoice_pdf'	=> online_buchhaltung_1und1_api_get_refund_pdf( $refund )
	);

	$order = wc_get_order( $refund->get_parent_id() );

	// build temp file, may throws an error and exits
	$args[ 'temp_file' ] = online_buchhaltung_1und1_api_build_temp_file( $args );

	// create customer or update user data
	$args[ 'customer' ] = online_buchhaltung_1und1_api_contact( $order->get_user_id(), $args );

	// send voucher to 1und1 online-buchhaltung
	$voucher_id = online_buchhaltung_1und1_api_send_voucher_refund( $args );

	// save 1und1 online-buchhaltung id as post meta
	update_post_meta( $refund->get_id(), '_online_buchhaltung_1und1_has_transmission', $voucher_id );

	return 'SUCCESS';

}

/**
* send refund as voucher to 1und1 online-buchhaltung
*
* @param Array $args
* @return String
*/
function online_buchhaltung_1und1_api_send_voucher_refund( $args ) {

	// init
	$refund = $args[ 'refund' ];
	$voucherPos = array();
	$accountingType= array ( 
		'id' => 27,
		'objectName' => 'AccountingType'
	);
	$complete_refund_amount = $refund->get_amount() * ( -1 );
	$item_sum_refunded = 0.0;
	$refund_reason = $refund->get_reason() == '' ? '' : sprintf( __( '(%s)', 'woocommerce-german-market' ), $refund->get_reason() );

	///////////////////////////////////
	// build voucher positions, 1st: order items
	///////////////////////////////////
	foreach ( $refund->get_items() as $item ) {
		
		if ( ! ( abs( $refund->get_line_total( $item, true, true ) ) > 0.0 ) ) {
			continue;
		} 

		$tax_gross_minus_net    = $refund->get_item_subtotal( $item, true, false ) - $refund->get_item_subtotal( $item, false, false );
		$tax_rate 				= round( $tax_gross_minus_net / $refund->get_item_subtotal( $item, false, true ) * 100, 1 );

		$voucherPos[] = apply_filters( 'online_buchhaltung_1und1_api_voucher_pos_refund', 
			
			array(
				'sum'			=> abs( $refund->get_line_total( $item, false, false ) ),
				'net'			=> 'false',
				'objectName'	=> 'VoucherPos',
				'accountingType'=> $accountingType,
				'mapAll' 		=> 'true',
				'comment' 		=> trim( __( 'Refund', 'woocommerce-german-market' ) . ': ' . $item[ 'name' ] . ' ' . $refund_reason ),
				'taxType'		=> 'default',
				'taxRate'		=> $tax_rate,
			),
			$item,

			$args[ 'refund' ]
		);

		$item_sum_refunded += abs( $refund->get_line_total( $item, true, true ) );

	}

	///////////////////////////////////
	// Shipping
	///////////////////////////////////
	$shipping = floatval( $refund->get_total_shipping() );
	$shipping_tax = floatval( $refund->get_shipping_tax() );
	$shipping_gross = floatval( $shipping + $shipping_tax );

	if ( abs( $shipping_gross ) > 0.0 ) {
		
		$item_sum_refunded += abs( $shipping_gross );

		$shipping_rate = round( $shipping_tax / $shipping * 100, 1 );
		
		$voucherPos[] = apply_filters( 'online_buchhaltung_1und1_api_voucher_pos_general_refund', 
				
			array(
				'sum'			=> abs( $shipping ),
				'net'			=> 'false',
				'objectName'	=> 'VoucherPos',
				'accountingType'=> $accountingType,
				'mapAll' 		=> 'true',
				'comment' 		=> sprintf( __( 'Refund Shipping: %s', 'woocommerce-german-market' ), $refund->get_shipping_method() ),
				'taxType'		=> 'default',
				'taxRate'		=> $shipping_rate,
			),

			$args[ 'refund' ]
		);
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

			$item_sum_refunded += abs( $fee_gross );
			$fee_rate = round( $fee_tax / $fee_total * 100, 1 );

			$voucherPos[] = apply_filters( 'online_buchhaltung_1und1_api_voucher_pos_general_refund', 
				
				array(
					'sum'			=> abs( $fee_total ),
					'net'			=> 'false',
					'objectName'	=> 'VoucherPos',
					'accountingType'=> $accountingType,
					'mapAll' 		=> 'true',
					'comment' 		=> sprintf( __( 'Refund Fee: %s', 'woocommerce-german-market' ), $fee_name ),
					'taxType'		=> 'default',
					'taxRate'		=> $fee_rate,
				),

				$args[ 'refund' ]
			);


		}

	}

	///////////////////////////////////
	// general refund item or rounding ocrrection
	///////////////////////////////////
	if ( $item_sum_refunded < abs( $complete_refund_amount ) ) {

		$amount_of_general_refund = ( abs( $complete_refund_amount ) - $item_sum_refunded ) * ( -1 );

		if ( ! abs( round( $amount_of_general_refund, 2 ) == 0.0 ) ) {

			if ( abs( $amount_of_general_refund ) < 0.02 ) {
				$accountingType= array ( 
					'id' => 41,
					'objectName' => 'AccountingType'
				);
			}

			$voucherPos[] = apply_filters( 'online_buchhaltung_1und1_api_voucher_pos_general_refund', 
				
				array(
					'sum'			=> abs( $amount_of_general_refund ),
					'net'			=> 'false',
					'objectName'	=> 'VoucherPos',
					'accountingType'=> $accountingType,
					'mapAll' 		=> 'true',
					'comment' 		=> trim( __( 'General Refund', 'woocommerce-german-market' ) . ' ' . $refund_reason ),
					'taxType'		=> 'default',
					'taxRate'		=> 0,
				),

				$args[ 'refund' ]
			);
		}

	}

	///////////////////////////////////
	// build voucher
	///////////////////////////////////

	$refund_voucher_paid_status = ( $args[ 'order' ]->is_paid() && apply_filters( 'woocommerce_de_online_buchhaltung_1und1_mark_refund_as_paid', true ) ) ? 1000 : 100;

	$voucher = array(
		
		'voucher'=>array(
			'objectName'	=> 'Voucher',
			'mapAll'		=> 'true',
			'voucherDate'	=> apply_filters( 'online_buchhaltung_1und1_api_voucher_date', $refund->get_date_created()->format( 'Y-m-d' ), $refund ),
			'description'	=> apply_filters( 'online_buchhaltung_1und1_api_voucher_description', sprintf( __( 'Refund #%s for Order %s', 'woocommerce-german-market' ), $refund->get_id(), $args[ 'order' ]->get_order_number() ), $args ),
			'status'		=> $refund_voucher_paid_status,
			'total'			=> abs( $complete_refund_amount ),
			'comment'		=> 'null',
			'payDate'		=> 'null',
			'taxType'		=> 'default',
			'creditDebit'	=> 'C',
			'voucherType'	=> 'VOU',
		),

		'filename' => $args[ 'temp_file' ],
		'voucherPosSave' => $voucherPos,
		'voucherPosDelete' => 'null'
	);

	// set customer
	if ( ! is_null( $args[ 'customer' ] ) ) {
		$voucher[ 'voucher' ][ 'supplier' ] = $args[ 'customer' ];
	}

	// filter
	$voucher = apply_filters( 'online_buchhaltung_1und1_api_set_voucher', $voucher, $args );

	$ch = curl_init();

	$data = http_build_query( $voucher, '', '&', PHP_QUERY_RFC1738 );

	curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . 'Voucher/Factory/saveVoucher' );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Authorization:' . $args[ 'api_token' ] ,'Content-Type:application/x-www-form-urlencoded' ) );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );

	$response = curl_exec( $ch );
	$result_array = json_decode( $response, true );

	curl_close( $ch );

	// error handling
	if ( ! isset( $result_array[ 'objects' ][ 'voucher' ][ 'id' ] ) ) {
		if ( isset( $result_array[ 'error' ][ 'message' ] ) ) {
			echo online_buchhaltung_1und1_api_get_error_message( $result_array[ 'error' ][ 'message' ] );
		} else {
			echo online_buchhaltung_1und1_api_get_error_message( __( 'Voucher could not be sent', 'woocommerce-german-market' ) );
		}
		exit();
	}

	$voucher_id = $result_array[ 'objects' ][ 'voucher' ][ 'id' ];

	// if order is paid
	if ( apply_filters( 'woocommerce_de_1und1_online_buchhaltung_mark_refund_as_paid', true ) ) {

		$book_account = apply_filters( 'woocommerce_de_1und1_online_buchhaltung_check_account', get_option( 'woocommerce_de_1und1_online_buchhaltung_check_account', '' ) );

		if ( $book_account != '' ) {
			
			$completed_date = date( 'Y-m-d H:i' );
			$sum_gross = 0.0;
			foreach ( $result_array[ 'objects' ][ 'voucherPos' ] as $voucherPos ) {
				$sum_gross += $voucherPos[ 'voucher' ][ 'sumGross' ];
			}
			
			$data_array = array(
				'ammount'					=> $sum_gross,
				'date'						=> strtotime( $completed_date ),
				'type'						=> 'null',
				'checkAccount' 				=> array(
												'id' 			=> $book_account,
												'objectName' 	=> 'CheckAccount',
												),
				'checkAccountTransaction'	=> 'null',
				'createFeed'				=> true
			);

			$data = http_build_query( $data_array, '', '&', PHP_QUERY_RFC1738 );

			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . 'Voucher/' . $voucher_id . '/bookAmmount/' );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
			curl_setopt( $ch, CURLOPT_PUT, 1 );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Authorization:' . $args[ 'api_token' ] ,'Content-Type:application/x-www-form-urlencoded' ) );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			$response = curl_exec( $ch );
			online_buchhaltung_1und1_api_curl_error_validaton( $response );

		}

	}

	return $result_array[ 'objects' ][ 'voucher' ][ 'id' ];

}

/**
* send order as voucher to 1und1 online-buchhaltung
*
* @param Array $args
* @return String
*/
function online_buchhaltung_1und1_api_send_voucher( $args ) {
	
	// init
	$order = $args[ 'order' ];
	$voucherPos = array();
	$sum_totals_splitted = array();
	$total_without_fees_and_shipping = 0.0;
	$total_gross = 0.0;

	// 26 == revenues
	// 27 == sales deduction
	// 41 == rounding differences

	///////////////////////////////////
	// build voucher positions, 1st: order items
	///////////////////////////////////
	$accountingType= array ( 
		'id' => 26,
		'objectName' => 'AccountingType'
	);

	foreach ( $order->get_items() as $item ) {

		$line_quantity = floatval( $item[ 'qty' ] );
		$item_tax = $order->get_item_tax( $item, false );

		$tax_rate = round( ( $item_tax * $line_quantity ) / $order->get_line_subtotal( $item, false, false ) * 100, 1 );

		// when coupons are applied or an refund has been made later, tax rate is maybe set to zero, correct it in the following lines
		if ( $tax_rate == 0 || ( $tax_rate != 7 && $tax_rate != 19 && $tax_rate != 0.0 ) ) {
			$item_gross = $order->get_line_subtotal( $item, true, false );
			$item_net 	= $order->get_line_subtotal( $item, false, false );
			$item_tax 	= $item_gross - $item_net;

			$maybe_tax_rate = round( ( $item_tax ) / $item_net * 100, 1 );

			if ( $maybe_tax_rate > 0 ) {
				$tax_rate = $maybe_tax_rate;
			}

		} 

		if ( ! isset( $sum_totals_splitted[ $tax_rate ] ) ) {
			$sum_totals_splitted[ $tax_rate ] = 0.0;
		}

		$sum_totals_splitted[ $tax_rate ] += $order->get_line_total( $item, false, false );

		// get sku
		$product = $item->get_product();
		$sku = $product->get_sku();
		if ( $sku != '' ) {
			$sku = ' ' . $sku . ' ';
		}

		$voucherPos[] = apply_filters( 'online_buchhaltung_1und1_api_voucher_pos', 
			
			array(
				'sum'			=> $order->get_line_subtotal( $item, false, false ),
				'net'			=> 'false',
				'objectName'	=> 'VoucherPos',
				'accountingType'=> $accountingType,
				'mapAll' 		=> 'true',
				'comment' 		=> sprintf( _x( '%sx%s%s', 'qty x(times) sku item names', 'woocommerce-german-market' ), $item[ 'qty' ], $sku, $item[ 'name' ] ),
				'taxType'		=> 'default',
				'taxRate'		=> $tax_rate,
			),

			$item, $args 

		);
		
		$total_without_fees_and_shipping += $order->get_line_total( $item, false, true );
		$total_gross += $order->get_line_subtotal( $item, true, true );
	}

	///////////////////////////////////
	// build voucher positions, 2nd: discounts (tax splitted)
	///////////////////////////////////
	$accountingType= array ( 
		'id' => 27,
		'objectName' => 'AccountingType'
	);

	$discount_net_splitted = array();
	$discount_gross_splitted = array();

	foreach ( $order->get_items() as $item ) {

		// init
		$tax_rate 		= round( $order->get_line_tax( $item ) / $order->get_line_total( $item, false, true ) * 100, 1 );

		// when coupons are applied, tax rate is maybe set to zero, correct it in the following lines
		if ( $tax_rate == 0 ) {
			$item_gross = $order->get_line_subtotal( $item, true, false );
			$item_net 	= $order->get_line_subtotal( $item, false, false );
			$item_tax 	= $item_gross - $item_net;

			$maybe_tax_rate = round( ( $item_tax ) / $item_net * 100, 1 );

			if ( $maybe_tax_rate > 0 ) {
				$tax_rate = $maybe_tax_rate;
			}

		} 

		if ( ! isset( $discount_net_splitted[ $tax_rate ] ) ) {
			$discount_net_splitted[ $tax_rate ] = 0.0;
		}

		if ( ! isset( $discount_gross_splitted[ $tax_rate ] ) ) {
			$discount_gross_splitted[ $tax_rate ] = 0.0;
		}

		$discount_net 	= $order->get_line_total( $item, false, false ) - $order->get_line_subtotal( $item, false, false );
		$discount_gross	= $order->get_line_total( $item, true, false ) - $order->get_line_subtotal( $item, true, false );

		// continue if there is no disocunt
		if ( ! $discount_net > 0.0 ) {
			continue;
		}

		$discount_net_splitted[ $tax_rate ] += $discount_net;
		$discount_gross_splitted[ $tax_rate ] += $discount_gross;

	}

	foreach ( $discount_net_splitted as $tax_rate => $discount_sum ) {
		
		// continue if there is no discount
		if ( ! $discount_sum > 0.0 ) {
			continue;
		}

		$voucherPos[] = apply_filters( 'online_buchhaltung_1und1_api_voucher_pos_discount', 
			
			array(
				'sum'			=> round( $discount_sum, 2 ),
				'net'			=> 'false',
				'objectName'	=> 'VoucherPos',
				'accountingType'=> $accountingType,
				'mapAll' 		=> 'true',
				'comment' 		=> __( 'Discount', 'woocommerce-german-market' ),
				'taxType'		=> 'default',
				'taxRate'		=> $tax_rate,
			),

			$args 
		);

		$total_gross += round( $discount_sum * ( 100 + $tax_rate ) / 100, 2 );

	}

	///////////////////////////////////
	// build voucher positions, 3rd: shipping (tax splitted)
	///////////////////////////////////
	if ( floatval( $order->get_total_shipping() ) > 0.0 ) {
	
		$accountingType= array ( 
			'id' => 26,
			'objectName' => 'AccountingType'
		);

		$shipping_split_tax = WGM_Tax::calculate_split_rate( $order->get_total_shipping(), $order, FALSE, '', 'shipping', false, false );

		if ( get_option( 'wgm_use_split_tax', 'on' ) == 'on' ) {

			$shipping_rates = $shipping_split_tax[ 'rates' ];

			foreach ( $shipping_rates as $shipping_rate ) {

				if ( $sum_totals_splitted[ floatval( $shipping_rate[ 'rate' ] ) ] >= 0.0 ) {

					// shipping part net
					$this_shipping_part_net 	= round( $sum_totals_splitted[ floatval( $shipping_rate[ 'rate' ] ) ], 2 ) / $total_without_fees_and_shipping * $order->get_total_shipping();

					$voucherPos[] = apply_filters( 'online_buchhaltung_1und1_api_voucher_pos_shipping', 
					
						array(
							'sum'			=> round( $this_shipping_part_net, 2 ),
							'net'			=> 'false',
							'objectName'	=> 'VoucherPos',
							'accountingType'=> $accountingType,
							'mapAll' 		=> 'true',
							'comment' 		=> sprintf( __( 'Shipping: %s', 'woocommerce-german-market' ), $order->get_shipping_method() ),
							'taxType'		=> 'default',
							'taxRate'		=> round( $shipping_rate[ 'rate' ], 1 ),
						),

						$args 
					);

					$total_gross += round( round( $this_shipping_part_net, 2 ) * ( 100 + $shipping_rate[ 'rate' ] ) / 100.0, 2 );

				}

			}

			if ( empty( $shipping_rates ) ) {

				$voucherPos[] = apply_filters( 'online_buchhaltung_1und1_api_voucher_pos_shipping', 
					
					array(
						'sum'			=> $order->get_total_shipping(),
						'net'			=> 'false',
						'objectName'	=> 'VoucherPos',
						'accountingType'=> $accountingType,
						'mapAll' 		=> 'true',
						'comment' 		=> sprintf( __( 'Shipping: %s', 'woocommerce-german-market' ), $order->get_shipping_method() ),
						'taxType'		=> 'default',
						'taxRate'		=> 0,
					),

					$args 
				);

				$total_gross += round( $order->get_total_shipping(), 2 );

			}

		} else {

			$shippings = $order->get_shipping_methods();
			
			foreach ( $shippings as $shipping ) {

				$shipping_tax = floatval( $shipping->get_total_tax() );
				$shipping_net = floatval( $shipping->get_total() );

				$tax_rate = round( $shipping_tax / $shipping_net * 100, 1 );

				$voucherPos[] = apply_filters( 'online_buchhaltung_1und1_api_voucher_pos_shipping', 
					
					array(
						'sum'			=> $shipping_net,
						'net'			=> 'false',
						'objectName'	=> 'VoucherPos',
						'accountingType'=> $accountingType,
						'mapAll' 		=> 'true',
						'comment' 		=> sprintf( __( 'Shipping: %s', 'woocommerce-german-market' ), $shipping->get_method_title() ),
						'taxType'		=> 'default',
						'taxRate'		=> $tax_rate,
					),

					$args 
				);

			}

			$total_gross += round( $order->get_total_shipping(), 2 ) + $order->get_shipping_tax();

		}

	}

	///////////////////////////////////
	// build voucher positions, 4th: fees (tax splitted)
	///////////////////////////////////
	$accountingType= array ( 
		'id' => 26,
		'objectName' => 'AccountingType'
	);

	// calc total fees
	$fee_total = 0.0;
	$fees = $order->get_fees();
	$fee_names = array();
	foreach ( $fees as $fee ) {
		$fee_names[] = $fee[ 'name' ];
		$fee_total += floatval( $fee[ 'line_total' ] );
	}

	if ( $fee_total > 0.0 ) {

		$fee_label = ( count( $fee_names ) > 1 ) ? __( 'Fees', 'woocommerce-german-market' ) : __( 'Fee', 'woocommerce-german-market' );
		$fee_split_tax = WGM_Tax::calculate_split_rate( $fee_total, $order, FALSE, '', 'fee', false, false );
		$fee_rates = $fee_split_tax[ 'rates' ];

		if ( get_option( 'wgm_use_split_tax', 'on' ) == 'on' ) {

			foreach ( $fee_rates as $fee_rate ) {

				if ( $sum_totals_splitted[ floatval( $fee_rate[ 'rate' ] ) ] >= 0.0 ) {

					// shipping part net
					$this_fee_part_net 	= round( $sum_totals_splitted[ floatval( $fee_rate[ 'rate' ] ) ], 2 ) / $total_without_fees_and_shipping * $fee_total;

					$voucherPos[] = apply_filters( 'online_buchhaltung_1und1_api_voucher_pos_fee', 
					
						array(
							'sum'			=> round( $this_fee_part_net, 2 ),
							'net'			=> 'false',
							'objectName'	=> 'VoucherPos',
							'accountingType'=> $accountingType,
							'mapAll' 		=> 'true',
							'comment' 		=> sprintf( _x( '%s: %s', 'Example: "Fee: Per Nachnahme" or "Fees: Per Nachnahme, Exportgebühr"', 'woocommerce-german-market' ), $fee_label, implode( ', ', $fee_names ) ),
							'taxType'		=> 'default',
							'taxRate'		=> round( $fee_rate[ 'rate' ], 1 ),
						),

						$args 
					);

					$total_gross += round( round( $this_fee_part_net, 2 ) * ( $fee_rate[ 'rate' ] + 100 ) / 100.0, 2 );

				}

			}

			if ( empty( $fee_rates ) ) {

				$voucherPos[] = apply_filters( 'online_buchhaltung_1und1_api_voucher_pos_fee', 
					
					array(
						'sum'			=> $fee_total,
						'net'			=> 'false',
						'objectName'	=> 'VoucherPos',
						'accountingType'=> $accountingType,
						'mapAll' 		=> 'true',
						'comment' 		=> sprintf( _x( '%s: %s', 'Example: "Fee: Per Nachnahme" or "Fees: Per Nachnahme, Exportgebühr"', 'woocommerce-german-market' ), $fee_label, implode( ', ', $fee_names ) ),
						'taxType'		=> 'default',
						'taxRate'		=> 0,
					),

					$args 
				);

				$total_gross += round( $fee_total, 2 );

			}

		} else {

			$fee_label = __( 'Fee', 'woocommerce-german-market' );

			foreach ( $order->get_fees() as $fee ) {

				$tax_rate = round( $fee->get_total_tax() / $fee->get_total() * 100, 2 );

				$voucherPos[] = apply_filters( 'online_buchhaltung_1und1_api_voucher_pos_fee', 
					
					array(
						'sum'			=> $fee->get_total(),
						'net'			=> 'false',
						'objectName'	=> 'VoucherPos',
						'accountingType'=> $accountingType,
						'mapAll' 		=> 'true',
						'comment' 		=> sprintf( _x( '%s: %s', 'Example: "Fee: Per Nachnahme" or "Fees: Per Nachnahme, Exportgebühr"', 'woocommerce-german-market' ), $fee_label, $fee->get_name() ),
						'taxType'		=> 'default',
						'taxRate'		=> $tax_rate,
					),

					$args 
				);

				$total_gross += round( round( $fee->get_total(), 2 ) * ( $tax_rate + 100 ) / 100.0, 2 );

			}

		}

	}

	///////////////////////////////////
	// build voucher positions, 5th: rounding correction
	///////////////////////////////////

	if ( round( $order->get_total(), 2 ) != round( $total_gross, 2 ) ) {

		$accountingType= array ( 
			'id' => 41,
			'objectName' => 'AccountingType'
		);

		$voucherPos[] = apply_filters( 'online_buchhaltung_1und1_api_voucher_pos_shipping', 
			
			array(
				'sum'			=> round( $order->get_total() - $total_gross, 2 ),
				'net'			=> 'false',
				'objectName'	=> 'VoucherPos',
				'accountingType'=> $accountingType,
				'mapAll' 		=> 'true',
				'taxType'		=> 'default',
				'taxRate'		=> 0,
				'comment'		=> apply_filters( 'online_buchhaltung_1und1_api_voucher_rounding_differences_label', __( 'Rounding differences', 'woocommerce-german-market' ) ),
			),

			$args 
		);

	}

	$total = 0 ;
	foreach ($voucherPos as $pos){
		$total += $pos['sum'];
	}

	///////////////////////////////////
	// build voucher
	///////////////////////////////////

	$voucher_paid_status = ( $order->is_paid() && apply_filters( 'woocommerce_de_1und1_online_buchhaltung_mark_voucher_as_paid_and_do_check_account', true ) ) ? 1000 : 100;

	// Get Voucher Date
	$voucher_date = $order->get_date_created()->format( 'Y-m-d' ); // Date Created of Order
	
	// Try to get invoice date
	$invoice_date = $voucher_date;
	$maybe_invoice_date = $order->get_meta( '_wp_wc_running_invoice_number_date' );
	if ( ! empty( $maybe_invoice_date ) ) {
		$invoice_date_time = new DateTime();
		$invoice_date_time->setTimestamp( $maybe_invoice_date );
		$invoice_date = $invoice_date_time->format( 'Y-m-d' );
	}

	if ( apply_filters( 'online_buchhaltung_1und1_api_use_invoice_date_as_voucher_date', false ) || ( get_option( 'woocommerce_de_1und1_online_buchhaltung_voucher_date', 'order_date' ) == 'invoice_date' ) ) {
		$voucher_date = $invoice_date;
	}

	$voucher = array(
		
		'voucher'=>array(
			'objectName'	=> 'Voucher',
			'mapAll'		=> 'true',
			'voucherDate'	=> $voucher_date,
			'description'	=> apply_filters( 'online_buchhaltung_1und1_api_voucher_description', sprintf( __( 'Order #%s', 'woocommerce-german-market' ), $args[ 'order']->get_order_number() ), $args ),
			'status'		=> $voucher_paid_status,
			'total'			=> $total,
			'comment'		=> 'null',
			'payDate'		=> 'null',
			'taxType'		=> 'default',
			'creditDebit'	=> 'D',
			'voucherType'	=> 'VOU',
		),

		'filename' => $args[ 'temp_file' ],
		'voucherPosSave' => $voucherPos,
		'voucherPosDelete' => 'null'
	);

	// set customer
	if ( ! is_null( $args[ 'customer' ] ) ) {
		$voucher[ 'voucher' ][ 'supplier' ] = $args[ 'customer' ];
	}

	// filter
	$voucher = apply_filters( 'online_buchhaltung_1und1_api_set_voucher', $voucher, $args );

	$ch = curl_init();

	$data = http_build_query( $voucher, '', '&', PHP_QUERY_RFC1738 );

	curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . 'Voucher/Factory/saveVoucher' );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Authorization:' . $args[ 'api_token' ] ,'Content-Type:application/x-www-form-urlencoded' ) );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );

	$response = curl_exec( $ch );
	$result_array = json_decode( $response, true );
	curl_close( $ch );

	// error handling
	if ( ! isset( $result_array[ 'objects' ][ 'voucher' ][ 'id' ] ) ) {
		if ( isset( $result_array[ 'error' ][ 'message' ] ) ) {
			echo online_buchhaltung_1und1_api_get_error_message( $result_array[ 'error' ][ 'message' ] );
		} else {
			echo online_buchhaltung_1und1_api_get_error_message( __( 'Voucher could not be sent', 'woocommerce-german-market' ) );
		}
		exit();
	}

	$voucher_id = $result_array[ 'objects' ][ 'voucher' ][ 'id' ];
	
	// if order is paid
	if ( $order->is_paid() && apply_filters( 'woocommerce_de_1und1_online_buchhaltung_mark_voucher_as_paid_and_do_check_account', true ) ) {

		$book_account = apply_filters( 'woocommerce_de_1und1_online_buchhaltung_check_account', get_option( 'woocommerce_de_1und1_online_buchhaltung_check_account', '' ) );

		if ( $book_account != '' ) {

			$completed_date = get_post_meta( $order->get_id(), '_completed_date', true );

			$sum_gross = 0.0;
			foreach ( $result_array[ 'objects' ][ 'voucherPos' ] as $voucherPos_elem ) {
				$sum_gross += $voucherPos_elem[ 'sumGross' ];
			}
			
			$data_array = array(
				'ammount'					=> $sum_gross,
				'date'						=> strtotime( $completed_date ),
				'type'						=> 'null',
				'checkAccount' 				=> array(
												'id' 			=> $book_account,
												'objectName' 	=> 'CheckAccount',
												),
				'checkAccountTransaction'	=> 'null',
				'createFeed'				=> true
			);

			$data = http_build_query( $data_array, '', '&', PHP_QUERY_RFC1738 );

			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . 'Voucher/' . $voucher_id . '/bookAmmount/' );
			curl_setopt( $ch, CURLOPT_PUT, 1 );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Authorization:' . $args[ 'api_token' ] ,'Content-Type:application/x-www-form-urlencoded' ) );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			$response = curl_exec( $ch );

			online_buchhaltung_1und1_api_curl_error_validaton( $response );
			$result_array = json_decode( $response, true );

		}

	}

	return $voucher_id;
	
}

/**
* create or update user data in 1und1 online-buchhaltung
*
* @param Integer $wordpress_user_id
* @return Integer
*/
function online_buchhaltung_1und1_api_contact( $wordpress_user_id, $args ) {

	$return = null;

	// only if option is activated
	if ( get_option( 'woocommerce_de_1und1_online_buchhaltung_send_customer_data', 'off' ) == 'on' ) {
		
		// check if guest
		if ( $wordpress_user_id == 0 ) {
			return apply_filters( 'woocommerce_de_1und1_online_buchhaltung_send_customer_guest', null, $args );
		}

		// get 1und1 online-buchhaltung user
		$online_buchhaltung_user = array();
		$online_buchhaltung_user_customer_number = get_user_meta( $wordpress_user_id, '_1und1_online_buchhaltung_customer_number', true );

		// 1st try if user still exists
		if ( $online_buchhaltung_user_customer_number != '' ) {
			$online_buchhaltung_user = online_buchhaltung_1und1_api_contact_get_by_customer_number( $online_buchhaltung_user_customer_number, $args );
			if ( ! is_array( $online_buchhaltung_user ) ) {
				delete_user_meta( $wordpress_user_id, '_1und1_online_buchhaltung_customer_number' );
				delete_user_meta( $wordpress_user_id, '_1und1_online_buchhaltung_user_id' );
				delete_user_meta( $wordpress_user_id, '_1und1_online_buchhaltung_customer_company_number' );
				delete_user_meta( $wordpress_user_id, '_1und1_online_buchhaltung_company_id' );
				delete_user_meta( $wordpress_user_id, '_1und1_online_buchhaltung_customer__Email' );
				delete_user_meta( $wordpress_user_id, '_1und1_online_buchhaltung_customer__Phone' );
				delete_user_meta( $wordpress_user_id, '_1und1_online_buchhaltung_customer_billing_Address' );
				delete_user_meta( $wordpress_user_id, '_1und1_online_buchhaltung_customer_shipping_Address' );
				$online_buchhaltung_user_customer_number = '';
			} else {

				// re-save id (it may have change when switchen through 1und1 online-buchhaltung accounts)
				if ( isset( $online_buchhaltung_user[ 'id' ] ) ) {
					$old_user_id = get_user_meta( $wordpress_user_id, '_online_buchhaltung_1und1_user_id', true );
					if ( $old_user_id != $online_buchhaltung_user[ 'id' ] ) {
						delete_user_meta( $wordpress_user_id, '_1und1_online_buchhaltung_customer_number' );
						delete_user_meta( $wordpress_user_id, '_1und1_online_buchhaltung_user_id' );
						delete_user_meta( $wordpress_user_id, '_1und1_online_buchhaltung_customer_company_number' );
						delete_user_meta( $wordpress_user_id, '_1und1_online_buchhaltung_company_id' );
						delete_user_meta( $wordpress_user_id, '_1und1_online_buchhaltung_customer__Email' );
						delete_user_meta( $wordpress_user_id, '_1und1_online_buchhaltung_customer__Phone' );
						delete_user_meta( $wordpress_user_id, '_1und1_online_buchhaltung_customer_billing_Address' );
						delete_user_meta( $wordpress_user_id, '_1und1_online_buchhaltung_customer_shipping_Address' );
						update_user_meta( $wordpress_user_id, '_1und1_online_buchhaltung_user_id', $online_buchhaltung_user[ 'id' ] );	
					}
					
				}
			}

		}

		// create a new user
		if ( $online_buchhaltung_user_customer_number == '' ) {

			// build customer array
			$customer = online_buchhaltung_1und1_api_contact_build_customer_array( $wordpress_user_id );

			// do we have to create a company first?
			$add_company = apply_filters( 'online_buchhaltung_1und1_api_add_company', ( get_option( 'woocommerce_de_1und1_online_buchhaltung_customer_add_company', 'on') == 'on' ), $wordpress_user_id );

			if ( $add_company ) {
				$company = online_buchhaltung_1und1_api_contact_build_company_array( $wordpress_user_id );
				
				// add company
				if ( is_array( $company ) ) {

					$data = http_build_query( $company, '', '&', PHP_QUERY_RFC1738 );
					$ch = curl_init();
					curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . 'Contact/' );
					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
					curl_setopt( $ch, CURLOPT_HEADER, FALSE );
					curl_setopt( $ch, CURLOPT_POST, TRUE );
					curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
					curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
					  'Authorization:' . $args[ 'api_token' ],
					  'Content-Type: application/x-www-form-urlencoded'
					));
					$response = curl_exec( $ch );
					curl_close( $ch );
					online_buchhaltung_1und1_api_curl_error_validaton( $response );

					$response_array = json_decode( $response, true );
					$online_buchhaltung_commpany = $response_array[ 'objects' ];

					// save new 1und1 online-buchhaltung company data
					update_user_meta( $wordpress_user_id, '_1und1_online_buchhaltung_customer_company_number', $online_buchhaltung_commpany[ 'customerNumber' ] );
					update_user_meta( $wordpress_user_id, '_1und1_online_buchhaltung_company_id', $online_buchhaltung_commpany[ 'id' ] );

					// add company to customer array
					$customer[ 'parent' ] = array(
						'id' 			=> $online_buchhaltung_commpany[ 'id' ],
						'objectName'	=> 'Contact'
					);

				}

			}

			$data_customer = http_build_query( $customer, '', '&', PHP_QUERY_RFC1738 );

			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . 'Contact/' );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
			curl_setopt( $ch, CURLOPT_HEADER, FALSE );
			curl_setopt( $ch, CURLOPT_POST, TRUE );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $data_customer );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
			  'Authorization:' . $args[ 'api_token' ],
			  'Content-Type: application/x-www-form-urlencoded'
			));
			$response = curl_exec( $ch );
			curl_close( $ch );
			online_buchhaltung_1und1_api_curl_error_validaton( $response );

			// save new 1und1 online-buchhaltung user data
			$response_array = json_decode( $response, true );
			$online_buchhaltung_customer = $response_array[ 'objects' ];
			update_user_meta( $wordpress_user_id, '_1und1_online_buchhaltung_customer_number', $online_buchhaltung_customer[ 'customerNumber' ] );
			update_user_meta( $wordpress_user_id, '_1und1_online_buchhaltung_user_id', $online_buchhaltung_customer[ 'id' ] );
			
			$return = $online_buchhaltung_customer[ 'customerNumber' ];
			$online_buchhaltung_user_id = $online_buchhaltung_customer[ 'id' ];

			// add additional data
			online_buchhaltung_1und1_api_contact_add_data( 'addEmail', $wordpress_user_id, $online_buchhaltung_user_id, $args );
			online_buchhaltung_1und1_api_contact_add_data( 'addPhone', $wordpress_user_id, $online_buchhaltung_user_id, $args );
			online_buchhaltung_1und1_api_contact_add_data( 'addAddress', $wordpress_user_id, $online_buchhaltung_user_id, $args, 47 ); // billing address
			online_buchhaltung_1und1_api_contact_add_data( 'addAddress', $wordpress_user_id, $online_buchhaltung_user_id, $args, 48 ); // delivery address

			$return = online_buchhaltung_1und1_api_contact_get_by_customer_number( $online_buchhaltung_customer[ 'customerNumber' ], $args );

		} else {
			
			// user exists update all data
			$customer = online_buchhaltung_1und1_api_contact_build_customer_array( $wordpress_user_id );
			$data = http_build_query( $customer, '', '&', PHP_QUERY_RFC1738 );

			$ch = curl_init();
			$online_buchhaltung_user_id = get_user_meta( $wordpress_user_id, '_1und1_online_buchhaltung_user_id', true );
			curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . 'Contact/' . $online_buchhaltung_user_id );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
			curl_setopt( $ch, CURLOPT_HEADER, FALSE );
			curl_setopt( $ch, CURLOPT_POST, TRUE );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
			curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "PUT" );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
			  'Authorization:' . $args[ 'api_token' ],
			  'Content-Type: application/x-www-form-urlencoded'
			));
			$response = curl_exec( $ch );
			curl_close( $ch );
			online_buchhaltung_1und1_api_curl_error_validaton( $response );

			online_buchhaltung_1und1_api_contact_add_data( 'addEmail', $wordpress_user_id, $online_buchhaltung_user_id, $args, null, true );
			online_buchhaltung_1und1_api_contact_add_data( 'addPhone', $wordpress_user_id, $online_buchhaltung_user_id, $args, null, true );
			online_buchhaltung_1und1_api_contact_add_data( 'addAddress', $wordpress_user_id, $online_buchhaltung_user_id, $args, 47, true ); // billing address
			online_buchhaltung_1und1_api_contact_add_data( 'addAddress', $wordpress_user_id, $online_buchhaltung_user_id, $args, 48, true ); // delivery address

			$return = array(
				'id' => get_user_meta( $wordpress_user_id, '_1und1_online_buchhaltung_user_id', true ),
				'objectName' => 'Contact'
			);

		}

	}

	return $return;

}

/**
* build company array from wordpress user_id
*
* @param Integer $wordpress_user_id
* @return Mixed: false (no company) / Array
*/
function online_buchhaltung_1und1_api_contact_build_company_array( $wordpress_user_id ) {

	// init
	$company = false;
	$company_name = get_user_meta( $wordpress_user_id, 'billing_company', true );

	// if there is a company
	if ( trim( $company_name ) != '' ) {

		$company = array(
			'name'				=> $company_name,
			'customerNumber'	=> get_option( 'woocommerce_de_1und1_online_buchhaltung_customer_company_number_prefix', '' ) . $wordpress_user_id,
			'category'			=> array( 
									'id' 			=> 3, // customer
									'objectName'	=> 'Category'
								),
			'name2'				=> '',
			'description'		=> '',
			'vatNumber'			=> '',
			'bankAccount'		=> '',
			'bankNumber'		=> ''
		);

		$company = apply_filters( 'online_buchhaltung_1und1_api_customer_company_array', $company, $wordpress_user_id );

	}

	return $company;

}

/**
* build customer array from wordpress user_id
*
* @param Integer $wordpress_user_id
* @return Array
*/
function online_buchhaltung_1und1_api_contact_build_customer_array( $wordpress_user_id ) {

	$user_data = get_userdata( $wordpress_user_id );
	
	// because some admins did not saved first and last name
	$last_name = $user_data->last_name != '' ? $user_data->last_name : get_user_meta( $wordpress_user_id, 'billing_last_name', true );
	$first_name = $user_data->first_name != '' ? $user_data->first_name : get_user_meta( $wordpress_user_id, 'billing_first_name', true );

	$customer =  array(
		'familyname'		=> $last_name,
		'surename'			=> $first_name,
		'customerNumber'	=> get_option( 'woocommerce_de_1und1_online_buchhaltung_customer_number_prefix', '' ) . $wordpress_user_id,
		'category'			=> array( 
									'id' 			=> 3, // customer
									'objectName'	=> 'Category'
								), 
		'birthday'			=> null,
		'title'				=> null,
		'academicTitle' 	=> null,
		'gender'			=> null,
		'name2'				=> null,
		'description'		=> null,
		'vatNumber'			=> apply_filters( 'online_buchhaltung_1und1_api_customer_vat_number', null ),
		'bankAccount'		=> null,
		'bankNumber'		=> null,
	);

	return apply_filters( 'online_buchhaltung_1und1_api_customer_array', $customer, $wordpress_user_id );

}

/**
* add additional customer data
*
* @param String $endpoint
* @param Integer $wordpress_user_id
* @param Integer $online_buchhaltung_user_id
* @param Array $args
* @param Integer $address_category
* @return Array
*/
function online_buchhaltung_1und1_api_contact_add_data( $endpoint, $wordpress_user_id, $online_buchhaltung_user_id, $args, $address_category = 47, $update = false ) {

	$user_data = get_userdata( $wordpress_user_id );
	$post_meta_prefix = '';

	if ( $endpoint == 'addEmail' ) {

		$data = array(
			'key'	=> 2, // work
			'value'	=> $user_data->user_email,
			'type'	=> 2
		);

	} else if ( $endpoint == 'addPhone' ) {

		$data = array(
			'key'	=> 2, // work
			'value'	=> get_user_meta( $wordpress_user_id, 'billing_phone', true ),
			'type'	=> 2
		);

	} else if ( $endpoint == 'addAddress' ) {

		$post_meta_prefix = $address_category == 48 ? 'shipping' : 'billing';

		// get country
		$user_country = strtolower( get_user_meta( $wordpress_user_id, $post_meta_prefix . '_country', true ) );

		// get all country codes to get the id of the country
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . 'StaticCountry/?limit=999' );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $ch, CURLOPT_HEADER, FALSE );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
			'Authorization:' . $args[ 'api_token' ],
			'Content-Type: application/x-www-form-urlencoded'
		));
		$response = curl_exec( $ch );
		curl_close( $ch );
		online_buchhaltung_1und1_api_curl_error_validaton( $response );
		$response_array = json_decode( $response, true );
		$countries = $response_array[ 'objects' ];

		$data = array(
			'street'	=> trim( get_user_meta( $wordpress_user_id, $post_meta_prefix . '_address_1', true ) . ' ' . get_user_meta( $wordpress_user_id, $post_meta_prefix . '_address_2', true ) ),
			'zip'		=> get_user_meta( $wordpress_user_id, $post_meta_prefix . '_postcode', true ),
			'city'		=> get_user_meta( $wordpress_user_id, $post_meta_prefix . '_city', true ),
			'category'	=> $address_category,
			'type'		=> $address_category,
		);

		$data[ 'contact' ] = array(
			'id' => $online_buchhaltung_user_id,
			'objectName' => 'Contact'
		);

		// get country
		// pretend to be from Germany if we will not find the correct country
		$data[ 'country' ] = 1;

		foreach ( $countries as $country ) {
			// attention: a WooCommerce country code always consists of 2 letters (even if it should be 3)
			if ( substr( $country[ 'code' ], 0, 2 ) == $user_country ) {
				$data[ 'country' ] = $country[ 'id' ];
				break;
			}
		}

	}

	$post_meta_key = str_replace( 'add', '_1und1_online_buchhaltung_customer_' . $post_meta_prefix . '_', $endpoint );

	if ( ! $update ) {

		// add data
		$data = apply_filters( 'online_buchhaltung_1und1_api_customer_data_before_send', $data, $endpoint, $wordpress_user_id, $online_buchhaltung_user_id, $args, $address_category, $update );
		$data = http_build_query( $data, '', '&', PHP_QUERY_RFC1738 );
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . 'Contact/' . $online_buchhaltung_user_id . '/' . $endpoint );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $ch, CURLOPT_HEADER, FALSE );
		curl_setopt( $ch, CURLOPT_POST, TRUE );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
			'Authorization:' . $args[ 'api_token' ],
			'Content-Type: application/x-www-form-urlencoded'
		));
		$response = curl_exec( $ch );
		curl_close( $ch );

		online_buchhaltung_1und1_api_curl_error_validaton( $response );

		// Save id of CommunicationWay to update this data later
		$response_array = json_decode( $response, true );
		$id = $response_array[ 'objects' ][ 'id' ];
		update_user_meta( $wordpress_user_id, $post_meta_key, $id );

	} else {

		// change data for update
		$data[ 'key' ] = array(
			'id' => 2,
			'objectName' => 'CommunicationWayKey'
		);

		if ( isset( $data[ 'country' ] ) ) {
			$data[ 'country' ] = array(
				'id' => $data[ 'country' ],
				'objectName' => 'StaticCountry'
			);
		}

		if ( isset( $data[ 'category' ] ) ) {
			$data[ 'category' ] = array(
				'id' => $data[ 'category' ],
				'objectName' => 'Category'
			);
		}

		$data = apply_filters( 'online_buchhaltung_1und1_api_customer_data_before_send', $data, $endpoint, $wordpress_user_id, $online_buchhaltung_user_id, $args, $address_category, $update );
		$data = http_build_query( $data, '', '&', PHP_QUERY_RFC1738 );

		$communication_way_id = get_user_meta( $wordpress_user_id, $post_meta_key, true );
		
		$ch = curl_init();
		$api_endpoint = ( str_replace( 'Address', '', $post_meta_key ) != $post_meta_key ) ? 'ContactAddress' : 'CommunicationWay';
		curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . $api_endpoint . '/' . $communication_way_id );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt( $ch, CURLOPT_HEADER, FALSE);
		curl_setopt( $ch, CURLOPT_POST, TRUE );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "PUT" );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
			'Authorization:' . $args[ 'api_token' ],
			'Content-Type: application/x-www-form-urlencoded'
		));
		
		$response = curl_exec( $ch );
		curl_close( $ch );

		online_buchhaltung_1und1_api_curl_error_validaton( $response );

	}
}

/**
* get 1und1 online-buchhaltung_user bei online_buchhaltung_user_id
*
* @param Integer $online_buchhaltung_user_id
* @return -1 OR Array
*/
function online_buchhaltung_1und1_api_contact_get_by_customer_number( $online_buchhaltung_customer_number, $args ) {

	$return = -1;

	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . 'Contact/?customerNumber=' . $online_buchhaltung_customer_number . '&depth=true' );
	curl_setopt( $ch, CURLOPT_POST, 0 );
	curl_setopt( $ch,CURLOPT_HTTPHEADER,array( 'Authorization:' . $args[ 'api_token' ] ) );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
	$response = curl_exec( $ch );
	curl_close( $ch );
	$result_array = json_decode( $response, true );

	if ( isset( $result_array[ 'objects' ][ 0 ][ 'id' ] ) ) {
		$return = $result_array[ 'objects' ][ 0 ];
	}

	return $return;
}

/**
* build temp file of invoice pdf
*
* @param Array $args
* @return String
*/
function online_buchhaltung_1und1_api_build_temp_file( $args ) {

	$attachment = $args[ 'invoice_pdf' ];

	$cfile = new CURLFile( $attachment  );

	$post = array (
	    'file' => $cfile,
	);

	$curl = curl_init();

	curl_setopt_array( $curl, array(
	  CURLOPT_URL => $args[ 'base_url' ] . 'Voucher/Factory/uploadTempFile',
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_POSTFIELDS => $post,
	  CURLOPT_HTTPHEADER => array(
	    'accept: application/json',
	    'authorization: ' . $args[ 'api_token' ],
	    'cache-control: no-cache',
	    'content-type: multipart/form-data;',
	  ),
	) );
                                                                                                                                                                                                             
	$response = curl_exec( $curl );
	$error = curl_error( $curl );
	curl_close ( $curl );

	$response_array = json_decode( $response, true );

	// error handling
	if ( ! isset( $response_array[ 'objects' ][ 'filename' ] ) ) {

		if ( $error != '' ) {
			echo online_buchhaltung_1und1_api_get_error_message( $error );
		} else {
			echo online_buchhaltung_1und1_api_get_error_message( __( 'Failed to upload invoice pdf.', 'woocommerce-german-market' ) );
		}

		exit();
	}

	return $response_array[ 'objects' ][ 'filename' ];
	
} 

/**
* get voucher status (exists or not)
*
* @param Integer $args
* @return Boolean
*/
function online_buchhaltung_1und1_api_get_vouchers_status( $voucher_id ) {

	$curl = curl_init();

	curl_setopt_array( $curl, array(
	  CURLOPT_URL => online_buchhaltung_1und1_api_get_base_url() . 'Voucher/' . $voucher_id,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_HTTPHEADER => array(
	    'accept: application/json',
	    'authorization: ' . online_buchhaltung_1und1_api_get_api_token(),
	    'cache-control: no-cache',
	  ),
	) );

	$response = curl_exec( $curl );
	$response_array = json_decode( $response, true );

	if ( isset( $response_array[ 'error' ][ 'code' ] ) && $response_array[ 'error' ][ 'code' ] == 151 ) {
		return false;
	}

	return true;

}

/**
* Get api token
* @return String
*/
function online_buchhaltung_1und1_api_get_api_token() {

	$api_token = apply_filters( 'online_buchhaltung_1und1_api_get_api_token', get_option( 'woocommerce_de_1und1_online_buchhaltung_api_token', '' ) );
	
	if ( $api_token == '' && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		echo online_buchhaltung_1und1_api_get_error_message( __( 'There is no API token. Please go to the WooCommerce German Market settings and enter a valid API token.', 'woocommerce-german-market' ) );
		exit();
	}

	return $api_token;
}

/**
* Get invoice pdf, path to file
* @param WC_Order $order
* @return String
*/
function online_buchhaltung_1und1_api_get_invoice_pdf( $order ) {

	if ( ! class_exists( 'WP_WC_Invoice_Pdf_Create_Pdf' ) ) {
		echo online_buchhaltung_1und1_api_get_error_message( __( 'Modul Invoice PDF of WooCommerce German Market is not enabled.', 'woocommerce-german-market' ) );
		exit();
	}

	$args = array( 
			'order'				=> $order,
			'output_format'		=> 'pdf',
			'output'			=> 'cache',
			'filename'			=>  str_replace( '/', '-', apply_filters( 'wp_wc_invoice_pdf_frontend_filename', get_option( 'wp_wc_invoice_pdf_file_name_frontend', get_bloginfo( 'name' ) . '-' . __( 'Invoice-{{order-number}}', 'woocommerce-invoice-pdf' ) ), $order ) ),
		);
		
	$invoice 	= new WP_WC_Invoice_Pdf_Create_Pdf( $args );
  	$attachment = WP_WC_INVOICE_PDF_CACHE_DIR . $invoice->cache_dir . DIRECTORY_SEPARATOR . $invoice->filename;

  	return $attachment;
} 

/**
* Get refund pdf, path to file
* @param WC_Order $refund
* @return String
*/
function online_buchhaltung_1und1_api_get_refund_pdf( $refund ) {

	if ( ! class_exists( 'WP_WC_Invoice_Pdf_Create_Pdf' ) ) {
		echo online_buchhaltung_1und1_api_get_error_message( __( 'Modul Invoice PDF of WooCommerce German Market is not enabled.', 'woocommerce-german-market' ) );
		exit();
	}

	// init
	$refund_id 	= $refund->get_id();
	$order_id 	= $refund->get_parent_id();
	$order 		= wc_get_order( $order_id );

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
			);
	
	$refund = new WP_WC_Invoice_Pdf_Create_Pdf( $args );
	$attachment = WP_WC_INVOICE_PDF_CACHE_DIR . $refund->cache_dir . DIRECTORY_SEPARATOR . $refund->filename;

	remove_filter( 'wp_wc_invoice_pdf_template_invoice_content', array( 'WP_WC_Invoice_Pdf_Backend_Download', 'load_storno_template' ) );

	return $attachment;
} 

/**
* check if we can use the order
* @param WC_Order $order
* @return WC_Order
*/
function online_buchhaltung_1und1_api_check_order( $order ) {

	$error = '';

	/*
	if ( $order->get_status() != 'completed' ) {
		$error =  __( 'Order status is not completed. You can only send data to 1und1 online-buchhaltung if the order status is completed.', 'woocommerce-german-market' );
	}
	*/

	$error = apply_filters( 'online_buchhaltung_1und1_api_check_order', $error, $order );

	if ( $error != '' ) {
		echo online_buchhaltung_1und1_api_get_error_message( $error );
		exit();
	}

	return $order;

}

/**
* Markup for error message
* @param String $message
* @return String
*/
function online_buchhaltung_1und1_api_get_error_message( $message = '' ) {
	
	if ( $message == '' ) {
		$message = __( 'Unknown error.', 'woocommerce-german-market' );
	}
	
	return trim( __( '<b>ERROR:</b>', 'woocommerce-german-market' ) . ' ' . $message );
}

/**
* Check if curl response is an error
* @param String $response
* @return void (exit if error)
*/
function online_buchhaltung_1und1_api_curl_error_validaton( $response ) {

	$response_array = json_decode( $response, true );
	if ( isset( $response_array[ 'error' ] ) ) {
		echo online_buchhaltung_1und1_api_get_error_message( $response_array[ 'error' ][ 'message' ] );
		exit();
	}

}

/**
* get base_url
* @return String
*/
function online_buchhaltung_1und1_api_get_base_url() {
	return apply_filters( 'online_buchhaltung_1und1_api_get_base_url', 'https://online-buchhaltung.ionos.de/api/v1/' );
}
