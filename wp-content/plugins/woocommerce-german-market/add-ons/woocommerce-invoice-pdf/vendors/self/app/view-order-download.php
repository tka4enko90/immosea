<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_WC_Invoice_Pdf_View_Order_Download' ) ) {
	
	/**
	* frontend download on customer account, view-order
	*
	* @class WCREAPDF_View_Order_Download
	* @version	1.0
	* @category	Class
	*/
	class WP_WC_Invoice_Pdf_View_Order_Download {
		
		/**
		* download button on view-order page
		*
		* @since 0.0.1
		* @access public
		* @hook woocommerce_order_details_after_order_table
		* @return void
		*/
		public static function make_download_button ( $order ){
			
			if ( ! is_user_logged_in() ) {
				return;
			}

			// manual order confirmation
			if ( get_post_meta( $order->get_id(), '_gm_needs_conirmation', true ) == 'yes' ) {
				return;
			}

			// double-opt-in check
			if ( get_option( 'wgm_double_opt_in_customer_registration' ) == 'on' ) {
				
				$user = wp_get_current_user();
				$activation_status = get_user_meta( $user->ID, '_wgm_double_opt_in_activation_status', true );

				if ( $activation_status == 'waiting' ) {
					return;
				}

			}

			// if you don't set html5 attribut download and open link in current tab you get in chrome: Resource interpreted as Document but transferred with MIME type application
			$a_href			= esc_url( wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_wp_wc_invoice_pdf_view_order_invoice_download&order_id=' . $order->get_id() ), 'wp-wc-invoice-pdf-download' ) );
			$a_target       = ( get_option( 'wp_wc_invoice_pdf_view_order_link_behaviour', 'new' ) == 'new' ) ? 'target="_blank"' : '' ;
			$a_download     = ( get_option( 'wp_wc_invoice_pdf_view_order_download_behaviour', 'inline' ) == 'inline' ) ? '' : ' download' ;
			$a_attributes	= trim( $a_target . $a_download ); 
			$button_text    = get_option( 'wp_wc_invoice_pdf_view_order_button_text', __( 'Download Invoice Pdf', 'woocommerce-german-market' ) );
			$status    		= $order->get_status();
			if ( ( $status !== false ) && ( get_option( 'wp_wc_invoice_pdf_frontend_download_' . $status, 'no' ) == 'yes' ) ) {
				if( has_action( 'wp_wc_invoice_pdf_view_order_button' ) ) {
					do_action( 'wp_wc_invoice_pdf_view_order_button', $a_href, $a_target, $a_attributes, $button_text, $order );
				} else {
					?>
					<p class="download-invoice-pdf">
                    	<a href="<?php echo $a_href; ?>" class="button"<?php echo ( $a_attributes != '' ) ? ' ' . $a_attributes : ''; ?>><?php echo $button_text; ?></a>
					</p>
					<?php
				}
			}
		}
		
		/**
		* download pdf frontend
		*
		* @since 0.0.1
		* @access public
		* @hook wp_ajax_woocommerce_wcreapdf_view_order_download
		* @return void
		*/
		public static function download_pdf(){
			check_ajax_referer( 'wp-wc-invoice-pdf-download', 'security' );
			// init
			$order_id    = $_REQUEST[ 'order_id' ];
			$order       = new WC_Order( $order_id );
			$status      = $order->get_status();
			// creata pdf only if user is allowed to
			if ( ( $status !== false ) && ( current_user_can( 'view_order', $order_id ) ) ) {	
				do_action( 'wp_wc_invoice_pdf_before_frontend_download', $order );
				$args = array( 
						'order'				=> $order,
						'output_format'		=> 'pdf',
						'output'			=> get_option( 'wp_wc_invoice_pdf_view_order_download_behaviour' ),
						'filename'			=> apply_filters( 'wp_wc_invoice_pdf_frontend_filename', get_option( 'wp_wc_invoice_pdf_file_name_frontend', get_bloginfo( 'name' ) . '-' . __( 'Invoice-{{order-number}}', 'woocommerce-german-market' ) ), $order ),
					);
				$invoice = new WP_WC_Invoice_Pdf_Create_Pdf( $args );	
			} else {
				$redirect = apply_filters( 'wp_wc_invoice_pdf_view_order_redirect_link', get_permalink( get_option('woocommerce_myaccount_page_id' ) ) );
				wp_safe_redirect( absint( $redirect ) );	
			}
			exit();		
		}
		
	} // end class
	
} // end if
