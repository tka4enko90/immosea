<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCREAPDF_Email_Attachment' ) ) {
	
	/**
	* adds the pdf as an attachment to e-mails
	*
	* @class WCREAPDF_Email_Attachment
	* @version 1.0
	* @category	Class
	*/
	class WCREAPDF_Email_Attachment {
		
		/**
		* adds the pdf as an attachement to chosen customer e-mails
		*
		* @since 0.0.1
		* @access public
		* @static
		* @hook woocommerce_email_attachments
		* @return array $attachments
		*/
		public static function add_attachment( $attachments, $status , $order ) {
			if ( WCREAPDF_Helper::check_if_needs_attachement( $order ) ) {

				// init
				$allowed_stati 				= array( 'customer_order_confirmation', 'new_order', 'customer_invoice', 'customer_processing_order', 'customer_completed_order', 'customer_on_hold_order' );
				$allowed_stati				= apply_filters( 'wcreapdf_allowed_stati', $allowed_stati );
				$option_on_or_off			= 'off';
				// check if file has to be attached
				if ( isset( $status ) && in_array ( $status, $allowed_stati ) ) {
					$option_on_or_off = get_option( WCREAPDF_Helper::get_wcreapdf_optionname( $status ) );
					if ( $option_on_or_off == 'on' ) {
						$directory_name = WCREAPDF_Pdf::create_pdf( $order );
						do_action( 'wcreapdf_pdf_before_output', 'retoure', $order, false );
						$attachments[] 	= WCREAPDF_TEMP_DIR . 'pdf' .  DIRECTORY_SEPARATOR . $directory_name . DIRECTORY_SEPARATOR . get_option( WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_file_name' ), __( 'Retoure', 'woocommerce-german-market' ) ) . '.pdf';

					}	
				}
				
			}

			return $attachments;
		}
	} // end class
	
} // end if
