<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//////////////////////////////////////////////////
// init
//////////////////////////////////////////////////

$options	= array(	);
$description = __( 'You can give your customers the opportunity to download their invoices on ', 'woocommerce-german-market' ) . '"' . trim( __( 'Endpoint for the My Account &rarr; View Order page', 'woocommerce-german-market' ) ) . '"' . __( 'if the customer is logged in. You can decide for which order status the download button is available.', 'woocommerce-german-market' ) ;
$options[]	= array( 'title' => __( 'My Account Page', 'woocommerce-german-market' ), 'type' => 'title','desc' => $description, 'id' => 'wp_wc_invoice_pdf_my_account_page' );

$statusi	= wc_get_order_statuses();
$status_nr	= count( $statusi );
$i 			= 0;

//////////////////////////////////////////////////
// options
//////////////////////////////////////////////////

foreach ( $statusi as $status_key => $status_nice_name ) {

	$i++;
	if ( $i == 1 ) {
		$checkboxgroup = 'start';
	} else if ( $i == $status_nr ) {
		$checkboxgroup = 'ende';	
	} else {
		$checkboxgroup = 'wp_wc_invoice_pdf_checkboxgroup_my_account_page';	
	}
	
	$options[]		 = array(
						'title'         => __( 'Download Button', 'woocommerce-german-market' ),
						'desc'          => __( 'Enable download button for orders with status:', 'woocommerce-german-market' ) . ' "' . $status_nice_name . '"' ,
						'id'            => 'wp_wc_invoice_pdf_frontend_download_' . str_replace( 'wc-', '', $status_key ),
						'default'       => 'no',
						'type'          => 'checkbox',
						'checkboxgroup' => $checkboxgroup,
					);
}
	
$options[]	=	array(
					'name' 		=> __( 'Button Text', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Enter a text that is shown on the download button', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Enter a text that is shown on the download button', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_view_order_button_text',
					'type' 		=> 'text',
					'default'  	=> __( 'Download Invoice Pdf', 'woocommerce-german-market' ),
					'css'      	=> 'min-width:250px;',
				);
				
$options[]	=	array(
					'name' 		=> __( 'Link Behaviour', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Open the invoice link in a new browser tab or not. In the first case the HTML <code>&lt;a&gt;</code> tag gets the attribute <code>target="blank"</code>', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Open the invoice link in a new browser tab or not. In the first case the HTML <code>&lt;a&gt;</code> tag gets the attribute <code>target="blank"</code>', 'woocommerce-german-market' ),
					'id'  		=> 'wp_wc_invoice_pdf_view_order_link_behaviour',
					'type' 		=> 'select',
					'css'  		=> 'min-width:250px;',
					'default'  	=> 'new',
					'options' 	=> array(
						'new'  		=> __( 'New browser tab', 'woocommerce-german-market' ),
						'current'	=> __( 'Current browser tab', 'woocommerce-german-market' ),
						)
					);
				
$options[]	=	array(
					'name' 		=> __( 'Download Behaviour', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'If "Download" is selected the browser forces a file download. The HTML <code>&lt;a&gt;</code> tag gets the attribute <code>download</code> (HTML5). If "Inline" is selected the file will be send inline to the browser, i.e. the browser will try to open the file in a tab using a browser plugin to display pdf files if available', 'woocommerce-german-market' ),
					'tip'  		=> __( 'If "Download" is selected the browser forces a file download. The HTML <code>&lt;a&gt;</code> tag gets the attribute <code>download</code> (HTML5). If "Inline" is selected the file will be send inline to the browser, i.e. the browser will try to open the file in a tab using a browser plugin to display pdf files if available', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_view_order_download_behaviour',
					'type' 		=> 'select',
					'css'  		=> 'min-width:250px;',
					'default'  	=> 'inline',
					'options' 	=> array(
						'inline'	=> __( 'Inline', 'woocommerce-german-market' ),
						'download'	=> __( 'Download', 'woocommerce-german-market' ),
							)
					);
				
$options[]	=	array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_my_account_page' );
			