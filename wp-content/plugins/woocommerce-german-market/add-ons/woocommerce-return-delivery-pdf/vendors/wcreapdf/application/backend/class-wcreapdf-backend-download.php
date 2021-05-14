<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCREAPDF_Backend_Download' ) ) {
	
	/**
	* enables download buttons in backend
	*
	* @class WCREAPDF_Backend_Download
	* @version 1.0
	* @category	Class
	*/
	class WCREAPDF_Backend_Download {
		
		/**
		* adds 'create retoure pdf' to order actions options
		*
		* @since 3.9.2
		* @access public
		* @static
		* @hook woocommerce_order_actions
		* @arguments $array
		* @return $array ($actions => $optionname)
		* @hook woocommerce_order_actions_end
		* @arguments $post_id
		* @return void
		*/	
		public static function order_download( $post_id ) {
			$order = wc_get_order( $post_id );
			if ( WCREAPDF_Helper::check_if_needs_attachement( $order ) ) {	
				if ( apply_filters( 'german_market_backend_show_pdf_download_button', true, 'retoure', $post_id ) ) {
					echo '<li class="wide"><p><a class="button-primary" href="' . wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_wcreapdf_download&order_id=' . $post_id ), 'woocommerce-wcreapdf-download' ) . '">' . __( 'Download retoure pdf', 'woocommerce-german-market' ) . '</a></p></li>';
				}
			}
		}

		/**
		* adds 'create delivery pdf' to order actions options
		*
		* @since 3.9.2
		* @access public
		* @static
		* @hook woocommerce_order_actions
		* @arguments $array
		* @return $array ($actions => $optionname)
		* @hook woocommerce_order_actions_end
		* @arguments $post_id
		* @return void
		*/	
		public static function order_download_delivery( $post_id ) {
			$order = wc_get_order( $post_id );
			if ( WCREAPDF_Helper::check_if_needs_attachement( $order ) ) {	
				if ( apply_filters( 'german_market_backend_show_pdf_download_button', true, 'delivery', $post_id ) ) {
					$target = apply_filters( 'wcreapdf_backend_download_order_download_delivery_taget', '' );
					echo '<li class="wide"><p><a class="button-primary" ' . $target . ' href="' . wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_wcreapdf_download_delivery&order_id=' . $post_id ), 'woocommerce-wcreapdf-download' ) . '">' . __( 'Download delivery pdf', 'woocommerce-german-market' ) . '</a></p></li>';
				}
			}
		}
		
		/**
		* create the retoure pdf to shop user when choosing this option and force download
		*
		* @since 0.0.1
		* @access public
		* @static
		* @hook woocommerce_order_action_woocomerce_wcreapdf_sendretoure
		* @arguments $order
		* @return void
		*/		
		public static function order_action( $order ) {
			if ( WCREAPDF_Helper::check_if_needs_attachement( $order ) ) {	
				WCREAPDF_Pdf::create_pdf( $order, false, true, false, true );
			}
		}

		/**
		* create the delivery pdf to shop user when choosing this option and force download
		*
		* @since GM v3.2
		* @access public
		* @static
		* @arguments $order
		* @return void
		*/		
		public static function order_action_delivery( $order ) {
			if ( WCREAPDF_Helper::check_if_needs_attachement( $order ) ) {	
				WCREAPDF_Pdf_Delivery::create_pdf( $order, false, apply_filters( 'wcreapdf_backend_download_order_action_delivery_download', true ), false, true );
			}
		}
		
		/**
		* adds a small download button to the admin page for orders
		*
		* @since 0.0.1
		* @access public
		* @static 
		* @hook woocommerce_admin_order_actions
		* @arguments $actions, $theOrder
		* @return $actions
		*/	
		public static function admin_icon_download( $actions, $order ) {
			if ( WCREAPDF_Helper::check_if_needs_attachement( $order ) ) {
				$create_pdf = array( 
								'url' 		=>	wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_wcreapdf_download&order_id=' . $order->get_id() ), 'woocommerce-wcreapdf-download' ), 
								// would be nice do add html5 attribute download
								// so you get in chrome: Resource interpreted as Document but transferred with MIME type application
								'name' 		=> __( 'Download retoure pdf', 'woocommerce-german-market' ),
								'action' 	=> "retoure"
							);
				$actions[ 'retoure' ]	= $create_pdf;	
			}
			return $actions;
		}

		/**
		* adds a small download button for delivery pdf to the admin page for orders
		*
		* @since GM v3.2
		* @access public
		* @static 
		* @hook woocommerce_admin_order_actions
		* @arguments $actions, $theOrder
		* @return $actions
		*/	
		public static function admin_icon_download_delivery( $actions, $order ) {
			if ( WCREAPDF_Helper::check_if_needs_attachement( $order ) ) {
				$create_pdf = array( 
								'url' 		=>	wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_wcreapdf_download_delivery&order_id=' . $order->get_id() ), 'woocommerce-wcreapdf-download' ), 
								// would be nice do add html5 attribute download
								// so you get in chrome: Resource interpreted as Document but transferred with MIME type application
								'name' 		=> __( 'Download delivery pdf', 'woocommerce-german-market' ),
								'action' 	=> "delivery_pdf"
							);
				$actions[ 'delivery' ]	= $create_pdf;	
			}
			return $actions;
		}
		
		/**
		* ajax, manages what happen when the downloadbutton on admin order page is clicked
		*
		* @since 0.0.1
		* @access public
		* @static 
		* @hook wp_ajax_woocommerce_wcreapdf_download
		* @arguments $_REQUEST[ 'order_id' ]
		* @return void, exit()
		*/	
		public static function admin_ajax_download_pdf() {
			check_ajax_referer( 'woocommerce-wcreapdf-download', 'security' );
			$order_id	= intval( $_REQUEST[ 'order_id' ] );
			$order 		= new WC_Order( $order_id );
			self::order_action( $order );
			exit();
		}

		/**
		* ajax, manages what happen when the downloadbutton on admin order page is clicked
		*
		* @since GM v3.2
		* @access public
		* @static 
		* @hook wp_ajax_woocommerce_wcreapdf_download
		* @arguments $_REQUEST[ 'order_id' ]
		* @return void, exit()
		*/	
		public static function admin_ajax_download_pdf_delivery() {
			check_ajax_referer( 'woocommerce-wcreapdf-download', 'security' );
			$order_id	= intval( $_REQUEST[ 'order_id' ] );
			$order 		= new WC_Order( $order_id );
			self::order_action_delivery( $order );
			exit();
		}
		
		/**
		* ajax, manages test pdf download
		*
		* @since 0.0.1
		* @access public
		* @static 
		* @hook wp_ajax_woocommerce_wcreapdf_download_test_pdf
		* @return void, exit()
		*/	
		public static function download_test_pdf() {
			check_ajax_referer( 'woocommerce-wcreapdf-download-test-pdf', 'security' );
			WCREAPDF_Pdf::create_pdf( NULL, true, 'D' );
			exit();
		}

		/**
		* ajax, manages test pdf download PDF Delivery
		*
		* @since GM v3.2
		* @access public
		* @static 
		* @hook wp_ajax_woocommerce_wcreapdf_download_test_pdf_delivery
		* @return void, exit()
		*/	
		public static function download_test_pdf_delivery() {
			check_ajax_referer( 'woocommerce-wcreapdf-download-test-pdf', 'security' );
			WCREAPDF_Pdf_Delivery::create_pdf( NULL, true, 'D' );
			exit();
		}

		/**
		* add bulk action download zip with pdfs
		*
		* @since 3.5
		* @access public
		* @static 
		* @hook admin_footer
		* @return void
		*/
		public static function bulk_admin_footer() {
			
			global $post_type;

			if ( 'shop_order' == $post_type ) {
				?>
				<script type="text/javascript">
				jQuery(function() {
					
					<?php if ( get_option( 'woocomerce_wcreapdf_wgm_pdf_backend_download', 'on' ) == 'on' ) { ?>

						jQuery('<option>').val('gm_download_retoure_zip').text("<?php _e( 'Downloads Retoure PDFs', 'woocommerce-german-market' ); ?>" ).appendTo('select[name="action"]');
						jQuery('<option>').val('gm_download_retoure_zip').text("<?php _e( 'Downloads Retoure PDFs', 'woocommerce-german-market' ); ?>" ).appendTo('select[name="action2"]');

					<?php } ?>

					<?php if ( get_option( 'woocomerce_wcreapdf_wgm_pdf_delivery_backend_download', 'on' ) == 'on' ) { ?>

						jQuery('<option>').val('gm_download_delivery_zip').text("<?php _e( 'Downloads Delivery PDFs', 'woocommerce-german-market' ); ?>").appendTo('select[name="action"]');
						jQuery('<option>').val('gm_download_delivery_zip').text("<?php _e( 'Downloads Delivery PDFs', 'woocommerce-german-market' ); ?>").appendTo('select[name="action2"]');

					<?php }

					?>
				});
				</script>
				<?php
			}
		}

		public static function bulk_action() {

			// return if no orders are checked
			if ( ! isset( $_REQUEST[ 'post' ] ) ) {
				return;
			}

			$post_ids = array_map( 'absint', (array) $_REQUEST[ 'post' ] );

			// return if no order is checked
			if ( empty( $post_ids ) ) {
				return;
			}

			$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
			$action        = $wp_list_table->current_action();

			self::clear_temp_hard_pdf();

			$files = array();
			// return if it's not the zip download action
			if ( $action == 'gm_download_retoure_zip' ) {
				
				foreach ( $post_ids as $post_id ) {

					$order = wc_get_order( $post_id );

					$files[] = WCREAPDF_Pdf::create_pdf( $order, false, false, true, true );

				}

				// create zip file
				$zip = new ZipArchive();
				$zip_dir  = untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'woocommerce-return-delivery-pdf-zip' . DIRECTORY_SEPARATOR;
				wp_mkdir_p( $zip_dir );
				$zip_file = $zip_dir . time() . "_" . rand( 1, 99999 ) . '_' . md5( rand( 1, 999999 ) . 'wp_wc_return_delivery_pdf' ) . md5( 'woocommerce-return-delivery-pdf' . rand( 0, 999999 ) ) . '.zip';

				if ( $zip->open( $zip_file, ZipArchive::CREATE ) ) {

					$files = array_diff( scandir( $zip_dir ), array( '.', '..' ) );

					foreach ( $files as $file ) {
						$zip->addFile( $zip_dir . $file, $file );
					}

					$zip->close();

					// clear pdf cache
					self::clear_temp_hard_pdf( true );

					// download zip file
					header( 'Content-Type: application/zip');
					header( 'Content-disposition: attachment; filename=' . apply_filters( 'wp_wc_return_delivery_pdf_zipname', date( 'Y-m-d-H-i' ) . '-' . __( 'retoure', 'woocommerce-german-market' ) . '.zip' ) );
					header( 'Content-Length: ' . filesize( $zip_file ) );
					readfile( $zip_file );

					exit();

				}

			} else if ( $action == 'gm_download_delivery_zip' ) {

				foreach ( $post_ids as $post_id ) {

					$order = wc_get_order( $post_id );

					$files[] = WCREAPDF_Pdf_Delivery::create_pdf( $order, false, false, true, true );

				}

				// create zip file
				$zip = new ZipArchive();
				$zip_dir  = untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'woocommerce-return-delivery-pdf-zip' . DIRECTORY_SEPARATOR;
				wp_mkdir_p( $zip_dir );
				$zip_file = $zip_dir . time() . "_" . rand( 1, 9999 ) . '_' . md5( rand( 1, 9999 ) . 'wp_wc_return_delivery_pdf' ) . md5( 'woocommerce-return-delivery-pdf' . rand( 0, 999 ) ) . '.zip';

				if ( $zip->open( $zip_file, ZipArchive::CREATE ) ) {

					$files = array_diff( scandir( $zip_dir ), array( '.', '..' ) );

					foreach ( $files as $file ) {
						$zip->addFile( $zip_dir . $file, $file );
					}

					$zip->close();

					// clear pdf cache
					self::clear_temp_hard_pdf( true );

					// download zip file
					header( 'Content-Type: application/zip');
					header( 'Content-disposition: attachment; filename=' . apply_filters( 'wp_wc_return_delivery_pdf_zipname', date( 'Y-m-d-H-i' ) . '-' . __( 'Delivery-Note', 'woocommerce-german-market' ) . '.zip' ) );
					header( 'Content-Length: ' . filesize( $zip_file ) );
					readfile( $zip_file );

					exit();

				}

			}

		}

		private static function clear_temp_hard_pdf( $zip = false ) {
			
			$cache_dir 		= untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'woocommerce-return-delivery-pdf-zip' . DIRECTORY_SEPARATOR;

			if ( ! is_dir( $cache_dir ) ) {
				return;
			}

			$files = array_diff( scandir( $cache_dir ), array( '.', '..' ) );
			
			foreach ( $files as $file ) {
				
				if ( $zip ) {
					
					if ( str_replace( '.zip', '', $file ) != $file ) {
						continue;
					}

				}
				
				unlink( $cache_dir . DIRECTORY_SEPARATOR . $file );

			}
		}

		/**
		* Show error messages that accure when trying to create pdf
		*
		* @since 3.5.1
		* @access public
		* @static 
		* @hook admin_notices
		* @return void
		*/
		public static function show_error_message() {

			$error_message = get_option( 'wcreapdf_pdf_image_bind_error', '' );
			if ( $error_message != '' ) {
				
				?>
			    <div class="notice notice-error">
			        <p><?php echo $error_message; ?></p>
			    </div>
			    <?php

			}

			delete_option( 'wcreapdf_pdf_image_bind_error' );
		}

	} // end class
} // end if
