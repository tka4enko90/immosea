<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_WC_Invoice_Pdf_Email_Attachment' ) ) {
	
	/**
	* adds the pdf as an attachment to e-mails
	*
	* @class WP_WC_Invoice_Pdf_Email_Attachment
	* @version 1.0
	* @category	Class
	*/
	class WP_WC_Invoice_Pdf_Email_Attachment {
		
		/**
		* adds the pdf as an attachement to chosen customer e-mails
		*
		* @since 0.0.1
		* @access public
		* @static
		* @hook woocommerce_email_attachments
		* @param Array $attachments
		* @param String $status
		* @param WC_Order $order
		* @return Array
		*/
		public static function add_attachment( $attachments, $status , $order ) {
			  // init
			  $allowed_stati 	= array( 'customer_order_confirmation', 'new_order', 'customer_invoice', 'customer_processing_order', 'customer_completed_order', 'customer_on_hold_order', 'customer_note' );
			  $allowed_stati	= apply_filters( 'wp_wc_inovice_pdf_allowed_stati', $allowed_stati );
			  $option_on_or_off	= 'off';
			  // check if file has to be attached
			  if ( isset( $status ) && in_array ( $status, $allowed_stati ) && apply_filters( 'wp_wc_invoice_pdf_allowed_order', true, $order ) ) {
				  $option_on_or_off = get_option( 'wp_wc_invoice_pdf_emails_' . $status, 'off' );
				  if ( $option_on_or_off == 'on' ) {
						$args = array( 
								'order'				=> $order,
								'output_format'		=> 'pdf',
								'output'			=> 'cache',
								'filename'			=> self::repair_filename( apply_filters( 'wp_wc_invoice_pdf_frontend_filename', get_option( 'wp_wc_invoice_pdf_file_name_frontend', get_bloginfo( 'name' ) . '-' . __( 'Invoice-{{order-number}}', 'woocommerce-german-market' ) ), $order ) ),
							);
							
						do_action( 'wp_wc_invoice_before_adding_attachment', $status, $order );
						
						//remove_all_filters( 'wp_wc_invoice_pdf_template_invoice_content' );

						$invoice 		= new WP_WC_Invoice_Pdf_Create_Pdf( $args );
					  	$attachments[] 	= WP_WC_INVOICE_PDF_CACHE_DIR . $invoice->cache_dir . DIRECTORY_SEPARATOR . $invoice->filename;
				  }	
			  }
			return $attachments;
		}

		/**
		* triggers when order is refunded
		*
		* @since WGM 3.0
		* @access public
		* @static
		* @hook woocommerce_order_fully_refunded_notification
		* @hook woocommerce_order_partially_refunded_notification
		* @param int $order_id
	 	* @param int $refund_id
		* @return void
		*/
		public static function refunded_trigger( $order_id, $refund_id ) {

			if ( get_option( 'wp_wc_invoice_pdf_emails_customer_refunded_order' ) == 'on' ) {
				update_post_meta( $order_id, '_wp_wc_invoice_pdf_refund_id_for_email', $refund_id );
				do_action( 'wp_wc_invoice_before_adding_refund_attachment', $refund_id, $order_id );
				add_filter( 'woocommerce_email_attachments', array( 'WP_WC_Invoice_Pdf_Email_Attachment', 'add_refund_attachment' ), 10, 3 );
			}
			
			if ( get_option( 'wp_wc_invoice_pdf_emails_customer_refunded_order_add_pdfs' ) == 'on' ) {
				add_filter( 'woocommerce_email_attachments', array( 'WP_WC_Invoice_Pdf_Email_Attachment', 'trigger_refund_for_additional_pdfs' ), 10, 3 );
			}
		}

		/**
		* Do that trick for adding additional pdfs to customer refunded order
		*
		* @since WGM 3.0.2
		* @access public
		* @static
		* @hook woocommerce_email_attachments
		* @param Array $attachments
		* @param String $status
		* @param WC_Order $order
		* @return Array
		*/
		public static function trigger_refund_for_additional_pdfs( $attachments, $status , $order ) {
			return self::additional_email_attachments( $attachments, 'customer_refunded_order', $order );
		}

		/**
		* adds the refund pdf as an attachement
		*
		* @since WGM 3.0
		* @access public
		* @static
		* @hook woocommerce_email_attachments
		* @param Array $attachments
		* @param String $status
		* @param WC_Order $order
		* @return Array
		*/
		public static function add_refund_attachment( $attachments, $status , $order ) {

			// get refund id
			$refund_id = get_post_meta( $order->get_id(), '_wp_wc_invoice_pdf_refund_id_for_email', true );
			$refund = wc_get_order( $refund_id );

			// get filename
			$filename = get_option( 'wp_wc_invoice_pdf_refund_file_name_frontend', 'Refund-{{refund-id}} for order {{order-number}}' );
			// replace {{refund-id}}, the other placeholders will be managed by the class WP_WC_Invoice_Pdf_Create_Pdf
			$filename = str_replace( '{{refund-id}}', $refund_id, $filename );
			$filename = self::repair_filename( apply_filters( 'wp_wc_invoice_pdf_refund_frontend_filename', $filename, $refund ) );

			// change template
			add_filter( 'wp_wc_invoice_pdf_template_invoice_content', array( 'WP_WC_Invoice_Pdf_Backend_Download', 'load_storno_template' ) );

			$args = array( 
				'refund'			=> $refund,
				'order'				=> $order,
				'output_format'		=> 'pdf',
				'output'			=> 'cache',
				'filename'			=> $filename,
			);

			$refund 		= new WP_WC_Invoice_Pdf_Create_Pdf( $args );
			$attachments[] 	= WP_WC_INVOICE_PDF_CACHE_DIR . $refund->cache_dir . DIRECTORY_SEPARATOR . $refund->filename;

			// clear
			delete_post_meta( $order->get_id(), '_wp_wc_invoice_pdf_refund_id_for_email' );
			remove_filter( 'wp_wc_invoice_pdf_template_invoice_content', array( 'WP_WC_Invoice_Pdf_Backend_Download', 'load_storno_template' ) );

			return $attachments;

		}

		/**
		* adds additonal pdfs as an attachement to chosen customer e-mails
		*
		* @hook woocommerce_email_attachments
		* @param Array $attachments
		* @param String $status
		* @param WC_Order $order
		* @return Array
		* @return array $attachments
		*/
		public static function additional_email_attachments( $attachments, $status , $order ) {

			 // init
			$allowed_stati 	= array( 'customer_order_confirmation', 'new_order', 'customer_invoice', 'customer_processing_order', 'customer_completed_order', 'customer_on_hold_order', 'customer_refunded_order', 'customer_note' );
			$allowed_stati	= apply_filters( 'wp_wc_inovice_pdf_allowed_stati_additional_mals', $allowed_stati );
			$option_on_or_off	= 'off';
		  	
		  	// check if file has to be attached
			$option_on_or_off = get_option( 'wp_wc_invoice_pdf_emails_' . $status . '_add_pdfs', 'off' );
			if ( $option_on_or_off == 'on' ) {

				do_action( 'wp_wc_invoice_pdf_email_additional_attachment_before', array( 'order' => $order ) );

				if ( isset( $status ) && in_array ( $status, $allowed_stati ) ) {

					///////////////////////////
					// terms and conditions
					///////////////////////////
					if ( ! isset( $attachments[ 'german_market_terms_and_conditions' ] ) ) {

						// do whe have to inlcude this pdf?
						$options = array(
							'wp_wc_invoice_pdf_additional_pdf_legal_information_page',
							'wp_wc_invoice_pdf_additional_pdf_terms_page',
							'wp_wc_invoice_pdf_additional_pdf_privacy_page',
							'wp_wc_invoice_pdf_additional_pdf_shipping_and_delivery_page',
							'wp_wc_invoice_pdf_additional_pdf_payment_methods_page'
						);

						$include_terms_and_conditions_pdf = false;
						foreach ( $options as $option ) {
							if ( get_option( $option ) == 'yes' ) {
								$include_terms_and_conditions_pdf = true;
								break;
							}
						}

						$include_terms_and_conditions_pdf = apply_filters( 'wp_wc_invoice_pdf_include_terms_and_conditions_pdf', $include_terms_and_conditions_pdf, $order );

						if ( $include_terms_and_conditions_pdf ) {

							add_filter( 'wp_wc_invoice_pdf_template_invoice_content', array( __CLASS__, 'terms_and_conditions_content' ) );

							$args = array(
								'order'				=> $order,
								'output_format'		=> 'pdf',
								'output'			=> 'cache',
								'filename'			=> apply_filters( 'wp_wc_invoice_pdf_template_filename_termans_and_conditions', get_option( 'wp_wc_invoice_pdf_additional_pdfs_file_name_terms', __( 'Terms and conditions', 'woocommerce-german-market' ) ) ),
							);

							$invoice 		= new WP_WC_Invoice_Pdf_Create_Pdf( $args );
							$attachments[ 'german_market_terms_and_conditions' ] = WP_WC_INVOICE_PDF_CACHE_DIR . $invoice->cache_dir . DIRECTORY_SEPARATOR . $invoice->filename;

							remove_filter( 'wp_wc_invoice_pdf_template_invoice_content', array( __CLASS__, 'terms_and_conditions_content' ) );
						}

					}

					///////////////////////////
					// Recovation policy
					///////////////////////////
					if ( ! isset( $attachments[ 'german_market_revocation_policy' ] ) ) {
						
						$options = array(
							'wp_wc_invoice_pdf_additional_pdf_recovation_policy_page',
							'wp_wc_invoice_pdf_additional_pdf_recovation_policy_digital_page',
						);

						$include_revocation_pdf = false;
						foreach ( $options as $option ) {
							if ( get_option( $option ) == 'yes' ) {
								$include_revocation_pdf = true;
								break;
							}
						}

						$include_revocation_pdf = apply_filters( 'wp_wc_invoice_pdf_include_revocation_pdf', $include_revocation_pdf, $order );
						
						if ( $include_revocation_pdf ) {
							
							add_filter( 'wp_wc_invoice_pdf_template_invoice_content', array( __CLASS__, 'revocation_policy_content' ) );

							$args = array(
								'order'				=> $order,
								'output_format'		=> 'pdf',
								'output'			=> 'cache',
								'filename'			=> apply_filters( 'wp_wc_invoice_pdf_template_filename_revocation_policy', get_option( 'wp_wc_invoice_pdf_additional_pdfs_file_name_revocation', __( 'Revocation Policy', 'woocommerce-german-market' ) ) ),
							);

							$invoice 												= new WP_WC_Invoice_Pdf_Create_Pdf( $args );
							$attachments[ 'german_market_revocation_policy' ] 		= WP_WC_INVOICE_PDF_CACHE_DIR . $invoice->cache_dir . DIRECTORY_SEPARATOR . $invoice->filename;

							remove_filter( 'wp_wc_invoice_pdf_template_invoice_content',  array( __CLASS__, 'revocation_policy_content' ) );
						}
					}

				}
			}

			return $attachments;

		}

		/**
		* template for terms and conditions in pdf
		*
		* @hook wp_wc_invoice_pdf_template_invoice_content
		* @param String $path
		* @return String
		*/
		public static function terms_and_conditions_content( $path ) {

			$theme_template_file = get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'woocommerce-invoice-pdf' . DIRECTORY_SEPARATOR . 'terms-and-conditions.php';
			if ( file_exists( $theme_template_file ) ) {
				$template_path = $theme_template_file;
			} else {
				$template_path = untrailingslashit( plugin_dir_path( Woocommerce_Invoice_Pdf::$plugin_filename ) ) . DIRECTORY_SEPARATOR . 'vendors' . DIRECTORY_SEPARATOR . 'self' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'terms-and-conditions.php';
			}

			$template_path = apply_filters( 'wp_wc_invoice_attachment_terms_and_conditions_content', $template_path, $path );

			return $template_path;

		}

		/**
		* recovation policy in pdf
		*
		* @hook wp_wc_invoice_pdf_template_invoice_content
		* @param String $path
		* @return String
		*/
		public static function revocation_policy_content( $path ) {

			$theme_template_file = get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'woocommerce-invoice-pdf' . DIRECTORY_SEPARATOR . 'revocation-policy.php';
			if ( file_exists( $theme_template_file ) ) {
				$template_path = $theme_template_file;
			} else {
				$template_path = untrailingslashit( plugin_dir_path( Woocommerce_Invoice_Pdf::$plugin_filename ) ) . DIRECTORY_SEPARATOR . 'vendors' . DIRECTORY_SEPARATOR . 'self' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'revocation-policy.php';
			}

			$template_path = apply_filters( 'wp_wc_invoice_attachment_revocation_policy_content', $template_path, $path );
			
			return $template_path;
		}

		/**
		* Filename may not include '/'
		*
		* @since GM 3.5.4.
		* @param String $filename
		* @return String
		*/
		public static function repair_filename( $filename ) {
			return str_replace( '/', '-', $filename );
		}

	} // end class
	
} // end if
