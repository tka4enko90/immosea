<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

/**
* Backend Settings German Market 3.1
*
* wp-hook woocommerce_de_ui_left_menu_items
* @param Array $items
* @return Array
*/
function online_buchhaltung_1und1_de_ui_left_menu_items( $items ) {

	$items[ 321 ] = array( 
				'title'		=> __( '1&1 Online-Buchhaltung', 'woocommerce-german-market' ),
				'slug'		=> 'online-buchhaltung-1und1',
				'callback'	=>'online_buchhaltung_1und1_de_ui_render_options',
				'options'	=> 'yes'
		);

	return $items;
}

/**
* Render Options for global
* 
* @return void
*/
function online_buchhaltung_1und1_de_ui_render_options() {

	if ( isset ( $_REQUEST[ 'woocommerce_de_1und1_online_buchhaltung_api_token' ] ) ) {
		wp_safe_redirect( get_admin_url() . 'admin.php?page=german-market&tab=online-buchhaltung-1und1' );
	}

	$description = '';

	if ( ! function_exists( 'curl_init' ) ) {

		$description = '<span style="color: #f00;">' . __( 'The PHP cURL library seems not to be present on your server. Please contact your admin / webhoster.', 'woocommerce-german-market' ) . ' ' . __( 'The 1&1 Online-Buchhaltung Add-On will not work without the cURL library.', 'woocommerce-german-market' ) . '</span><br /><br />';

	}

	$description .= __( 'Please enter your API token in the field above. To retrieve your API token, log in to your <a href="https://online-buchhaltung.1und1.de/" target="_blank">1&1 Online-Buchhaltung</a> account and go to <i>settings -> user</i>. Select your user account, under „edit user“ you will find your API token at the bottom of the page.', 'woocommerce-german-market' );
	
	$settings[] = array(
		'name' => __( 'Authorization', 'woocommerce-german-market' ),
		'type' => 'title',
		'id'   => 'online-buchhaltung-1und1',
		'desc' => $description
	);

	$settings[] = array(
		'name'              => __( 'API Token', 'woocommerce-german-market' ),	
		'id'                => 'woocommerce_de_1und1_online_buchhaltung_api_token',
		'type'              => 'text',
		'css'				=> 'min-width: 300px; max-width: 100%;'
	);

	$settings[] = array( 'type' => 'sectionend', 'id' => 'online-buchhaltung-1und1' );

	$settings[] = array(
		'name' => __( 'Settings', 'woocommerce-german-market' ),
		'type' => 'title',
		'id'   => 'online-buchhaltung-1und1_settings',
	);

	$description = __( 'Activate this option to send data of your WooCommerce customers to 1&1 Online-Buchhaltung to be used there as contacts (customers).', 'woocommerce-german-market' );

	$settings[] = array(
		'name'				=> __( 'Send Customer Data', 'woocommerce-german-market' ),
		'desc_tip'			=> $description,
		'id'				=> 'woocommerce_de_1und1_online_buchhaltung_send_customer_data',
		'type'     			=> 'wgm_ui_checkbox',
		'default'			=> 'off',
	);

	if ( get_option( 'woocommerce_de_1und1_online_buchhaltung_send_customer_data', 'off' ) == 'on' ) {

		$description = __( 'Prefix for your 1&1 Online-Buchhaltung customer numbers for persons, followed by the wordpress user id.', 'woocommerce-german-market' );

		$settings[] = array(
			'name'				=> __( 'Prefix - Person Customer Number', 'woocommerce-german-market' ),
			'desc_tip'			=> $description,
			'id'				=> 'woocommerce_de_1und1_online_buchhaltung_customer_number_prefix',
			'type'              => 'text',
			'default'			=> '',
		);

		$description = __( 'If you activate this option, a company will be added as a contact to 1&1 Online-Buchhaltung if the user has an billing company in the user profile.', 'woocommerce-german-market' );

		$settings[] = array(
			'name'				=> __( 'Create Companies', 'woocommerce-german-market' ),
			'desc_tip'			=> $description,
			'id'				=> 'woocommerce_de_1und1_online_buchhaltung_customer_add_company',
			'type'              => 'wgm_ui_checkbox',
		);

		$description = __( 'Prefix for your 1&1 Online-Buchhaltung customer numbers for companies, followed by the wordpress user id (which is the same as for the person). Only usesd if you activate the setting "Create Companies"', 'woocommerce-german-market' );

		$settings[] = array(
			'name'				=> __( 'Prefix - Company Customer Number', 'woocommerce-german-market' ),
			'desc_tip'			=> $description,
			'id'				=> 'woocommerce_de_1und1_online_buchhaltung_customer_company_number_prefix',
			'type'              => 'text',
			'default'			=> '',
		);

	}

	if ( get_option( 'woocommerce_de_1und1_online_buchhaltung_api_token' ) != '' ) {

		if ( function_exists( 'curl_init' ) ) {
			
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, online_buchhaltung_1und1_api_get_base_url() . 'CheckAccount/?register=0' );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Authorization:' . get_option( 'woocommerce_de_1und1_online_buchhaltung_api_token' ) ,'Content-Type:application/x-www-form-urlencoded' ) );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			$response = curl_exec( $ch );
			$result_array = json_decode( $response, true );
			curl_close( $ch );

			$check_accounts = array();

			if ( isset ( $result_array[ 'objects' ] ) ) {

				foreach ( $result_array[ 'objects' ] as $check_account ) {
					$check_accounts[ $check_account [ 'id' ] ] = $check_account[ 'name' ];
				}

				$description = __( 'Choose your booking account to book your vouchers.', 'woocommerce-german-market' );

				$settings[] = array(
					'name'				=> __( 'Book Account', 'woocommerce-german-market' ),
					'desc_tip'			=> $description,
					'id'				=> 'woocommerce_de_1und1_online_buchhaltung_check_account',
					'type'				=> 'select',
					'options'			=> $check_accounts

				);
			}	

		}

	}

	$settings[] = array( 'type' => 'sectionend', 'id' => 'online-buchhaltung-1und1_settings' );

	$settings = apply_filters( 'online_buchhaltung_woocommerce_de_ui_render_options', $settings );
	return( $settings );

}

