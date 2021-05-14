<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_WC_Running_Invoice_Number_Backend_Output_Post' ) ) {

	/**
	* output on post.php
	*
	* @class WP_WC_Running_Invoice_Number_Backend_Output_Post
	* @version 1.0
	* @category	Class
	*/
	class WP_WC_Running_Invoice_Number_Backend_Output_Post {
		
		
		/**
		* add 'Invoice Number' and 'Invoice Date' to order data
		*
		* @since 0.0.1
		* @access public
		* @static		
		* @arguments WC_Order $order
		* @hook woocommerce_admin_order_data_after_order_details
		* @return void
		*/
		public static function order_data_after_order_details( $order ) {
	
			// output if this is no 'new' order		
			if ( get_current_screen()->action != 'add' ) {
				
				$invoice_number = get_post_meta( $order->get_id(), '_wp_wc_running_invoice_number', true );
				if ( empty( $invoice_number ) ) {
					delete_post_meta( $order->get_id(), '_wp_wc_running_invoice_number' );
				}	
				$invoice_date	= get_post_meta( $order->get_id(), '_wp_wc_running_invoice_number_date', true );
				$invoice_date	= ( $invoice_date == '' ) ? '' : date_i18n( 'Y-m-d', $invoice_date );

				?>
				<p class="form-field form-field-wide">
					<label for="order_invoice_number"><?php echo __( 'Invoice Number', 'woocommerce-german-market' ) ?>:</label>
					<input type="text" name="order_invoice_number" id="order_invoice_number" value="<?php echo $invoice_number; ?>"<?php echo ( $invoice_number != '' ) ? ' readonly' : ''; ?> />
                    <?php 
					$style_display = ( $invoice_number == '' ) ? 'none' : 'block';
					?><a class="wp_wc_invoice_remove_read_only" id="wp_wc_invoice_remove_read_only" style="text-decoration: underline; cursor: pointer; display: <?php echo $style_display; ?>"><?php echo __( 'Edit invoice number', 'woocommerce-german-market' ); ?></a><?php
					?>
				</p>			
                <p class="form-field form-field-wide">
					<label for="order_invoice_date"><?php echo __( 'Invoice Date', 'woocommerce-german-market' ) ?>:</label>
					<input type="text" class="date-picker-field" name="order_invoice_date" id="order_invoice_date" value="<?php echo $invoice_date; ?>" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
					<?php    
					if ( $invoice_number == '' ) {
						?><br /><a name="<?php echo $order->get_id(); ?>" class="wp_wc_invoice_generate" style="text-decoration: underline; cursor: pointer;"><?php echo __( 'Generate and save invoice number and invoice date', 'woocommerce-german-market' ); ?></a><?php
					}
					?>
				</p>
				<?php
			
			// output if it is a new order
			} else {
				$checked = get_option( 'wp_wc_running_invoice_number_generate_when_order_is_created', 'off' );
				$checked_attribute = ( $checked == 'on' ) ? ' checked="checked" ' : '';
				?>
                <p class="form-field form-field-wide">
                	<input type="checkbox" id="order_generate_invoice" name="order_generate_invoice"<?php echo $checked_attribute; ?>style="width: auto; float: left;"/>
                    <label for="order_generate_invoice"><?php echo __( 'Generate invoice number and invoice date when saving the order', 'woocommerce-german-market' ); ?></label>
                </p>
                <?php
			}
		}
		
		/**
		* generate and save invoice number and invoice date when clicked on generate
		*
		* @since 0.0.1
		* @access public
		* @static
		* @hook wp_ajax_wp_wc_running_invoice_number_ajax_backend_post
		* @return void
		*/
		public static function post_ajax() {
			if ( check_ajax_referer( 'wp_wc_running_invoice_number_nonce', 'security' ) ) {
				global $wp_locale;
				$order_id 				= absint( $_REQUEST[ 'order_id' ] );
				$order 					= wc_get_order( $_REQUEST[ 'order_id' ] );
				$running_invoice_number = new WP_WC_Running_Invoice_Number_Functions( $order );	
				echo $running_invoice_number->get_invoice_number() . '[[SEPARATOR]]' . date_i18n( 'Y-m-d', intval( $running_invoice_number->get_invoice_timestamp() ) );	
				exit();
			}
		}
		
		/**
		* save meta data
		*
		* @since 0.0.1
		* @access public
		* @static		
		* @hook woocommerce_process_shop_order_meta
		* @return void
		*/
		public static function save_meta_data( $post_id, $post ) {

			// if it's a new order	
			if ( isset( $_REQUEST[ 'order_generate_invoice' ] ) ) {
				$order 					= new WC_Order( $post_id );
				$running_invoice_number = new WP_WC_Running_Invoice_Number_Functions( $order );	
			} else {

				if ( isset( $_REQUEST[ 'wc_order_action' ] ) && $_REQUEST[ 'wc_order_action' ] == 'wp_wc_invoice_pdf_invoice' ) {
					return;
				}

				// invoice number
				if ( isset( $_REQUEST[ 'order_invoice_number' ] ) ) {
					
					$logging = apply_filters( 'german_market_invoice_number_logging', false );
					if ( $logging ) {
						$current_number = get_post_meta( $post->ID, '_wp_wc_running_invoice_number', true );
						if ( $current_number != $_REQUEST[ 'order_invoice_number' ] ) {
							$new_log = sprintf( 'Order: %s, Manual Saved Invoice Number Meta from %s to %s.', $post->ID, $current_number, $_REQUEST[ 'order_invoice_number' ] );
							$logger = wc_get_logger();
							$context = array( 'source' => 'german-market-invoice-number' );
							$logger->info( $new_log, $context );
						}
					}

					if ( ! empty( trim( $_REQUEST[ 'order_invoice_number' ] ) ) ) {
						update_post_meta( $post->ID, '_wp_wc_running_invoice_number', $_REQUEST[ 'order_invoice_number' ] );
					} else {
						delete_post_meta( $post->ID, '_wp_wc_running_invoice_number' );
					}
				}

				// invoice date
				if ( isset( $_REQUEST[ 'order_invoice_date' ] ) ) {
					update_post_meta( $post->ID, '_wp_wc_running_invoice_number_date', strtotime( $_REQUEST[ 'order_invoice_date' ] ) );	
				}
			}
		}
		
	} // end class
	
} // end if class exists 
