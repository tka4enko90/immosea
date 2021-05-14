<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

if ( ! class_exists( 'Bulk_Transmission_lexoffice' ) ) {

	class Bulk_Transmission_lexoffice {

		static $instance_counter = 0;

		function __construct() {

			if ( ! class_exists( 'WC_Action_Queue' ) ) {
				return;
			}

			if ( self::$instance_counter == 0 ) {

				if ( is_admin() ) {

					// orders
					add_action( 'admin_footer', 								array( __CLASS__ , 'bulk_admin_footer' ), 10 );
					add_action( 'load-edit.php', 								array( __CLASS__, 'bulk_action' ) );
					add_action( 'manage_posts_extra_tablenav', 					array( __CLASS__, 'info_about_scheduled_transmissions' ) );

					// refunds
					add_action( 'woocommerc_de_refund_before_list',				array( __CLASS__, 'refund_button' ), 20 );
					add_action( 'woocommerc_de_refund_after_list',				array( __CLASS__, 'refund_button' ), 20 );
					add_action( 'admin_init',									array( __CLASS__, 'bulk_action_refunds' ) );
					add_action( 'woocommerc_de_refund_before_list',				array( __CLASS__, 'info_about_scheduled_transmissions_refunds' ), 100 );

				}

				add_action( 'german_market_lexoffice_bulk_transmission', 			array( __CLASS__, 'transmit_one_order_via_bulk' ) );
				add_action( 'german_market_lexoffice_bulk_transmission_refund', 	array( __CLASS__, 'transmit_one_refund_via_bulk' ) );

			}

			self::$instance_counter++;

		}

		/**
		* submit button for refunds
		*
		* @since 3.1
		* @access public
		* @static 
		* @hook woocommerc_de_refund_after_list, woocommerc_de_refund_before_list
		* @return void
		*/
		public static function refund_button() {
			?><input class="button-primary" type="submit" name="transmit-to-lexoffice" value="<?php echo __( 'Transmit to lexoffice', 'woocommerce-german-market' ); ?>"/><?php
		}

		/**
		* bulk download for refunds
		*
		* @access public
		* @static 
		* @hook admin_init
		* @return void
		*/
		public static function bulk_action_refunds() {
			
			if ( isset( $_REQUEST[ 'transmit-to-lexoffice' ] ) ) {

				// check nonce
				if ( ! isset( $_REQUEST[ 'wgm_refund_list_nonce' ] ) ) {
					return;
				}

				if ( ! wp_verify_nonce( $_POST[ 'wgm_refund_list_nonce' ], 'wgm_refund_list' ) ) {
					?><div id="message" class="error notice" style="display: block;"><p><?php echo __( 'Sorry, something went wrong while downloading your refunds. Please, try again.', 'woocommerce-german-market' ); ?></p></div><?php
					return;
				} 

				// init refunds
				if ( ! isset( $_REQUEST[ 'refunds' ] ) ) {
					return;
				}

				$refunds = $_REQUEST[ 'refunds' ];

				// return if no order is checked
				if ( empty( $refunds ) ) {
					return;
				}

				foreach ( $refunds as $refund_id ) {

					$is_scheduled = get_post_meta( $refund_id, '_lexoffice_woocomerce_scheduled_for_transmission', true );

					if ( empty( $is_scheduled ) ) {

						WC()->queue()->add( 'german_market_lexoffice_bulk_transmission_refund', array( 'refund_id' => $refund_id ), 'german_market_lexoffice' );
						update_post_meta( $refund_id, '_lexoffice_woocomerce_scheduled_for_transmission', 'yes' );

					}

				}

			}

		}

		/**
		* show info of background transmission for refunds
		*
		* @access public
		* @static 
		* @hook woocommerc_de_refund_before_list
		* @return void
		*/
		public static function info_about_scheduled_transmissions_refunds() {

			$search_args = array(
				'hook' 		=> 'german_market_lexoffice_bulk_transmission_refund',
				'status'	=> ActionScheduler_Store::STATUS_PENDING,
				'per_page'	=> -1,
			);

			$search = WC()->queue()->search( $search_args );
			$nr_in_queue = count( $search );

			if ( $nr_in_queue > 0 ) {

				?><div class="lexoffice-info-bulk refunds"><p><?php
					echo sprintf( _n( 'In the background %s refund is currently transmitted to lexoffice.', 'In the background %s refunds are currently transferred to lexoffice.', $nr_in_queue, 'woocommerce-german-market' ), $nr_in_queue );
				?></p></div><?php

			} else {

				$args = array(
					'meta_key'     	=> '_lexoffice_woocomerce_scheduled_for_transmission',
					'meta_compare' 	=> 'EXISTS',
					'type' 			=> 'shop_order_refund',
				);

				$orders = wc_get_orders( $args );

				foreach ( $orders as $order ) {
					delete_post_meta( $order->get_id(), '_lexoffice_woocomerce_scheduled_for_transmission' );
				}

			}
		}

		/**
		* show info of background transmission for orders
		*
		* @access public
		* @static 
		* @hook manage_posts_extra_tablenav
		* @return void
		*/
		public static function info_about_scheduled_transmissions( $which ) {
			
			$screen = get_current_screen();

			if ( $which == 'top' && $screen->id == 'edit-shop_order' && apply_filters( 'lexoffice_woocommerce_show_bulk_transmission_info', true ) ) {

				$search_args = array(
					'hook' 		=> 'german_market_lexoffice_bulk_transmission',
					'status'	=> ActionScheduler_Store::STATUS_PENDING,
					'per_page'	=> -1,
				);

				$search = WC()->queue()->search( $search_args );
				$nr_in_queue = count( $search );

				if ( $nr_in_queue > 0 ) {

					?><div class="lexoffice-info-bulk orders"><p><?php
						echo sprintf( _n( 'In the background %s order is currently transmitted to lexoffice.', 'In the background %s orders are currently transferred to lexoffice.', $nr_in_queue, 'woocommerce-german-market' ), $nr_in_queue );
					?></p></div><?php

				} else {

					$args = array(
						'meta_key'     	=> '_lexoffice_woocomerce_scheduled_for_transmission',
						'meta_compare' 	=> 'EXISTS',
					);

					$orders = wc_get_orders( $args );

					foreach ( $orders as $order ) {
						delete_post_meta( $order->get_id(), '_lexoffice_woocomerce_scheduled_for_transmission' );
					}

				}

			}

		}

		/**
		* add bulk action
		*
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

					jQuery('<option>').val( 'gm_lexoffice_bulk_transmission' ).text("<?php _e( 'Transmit to lexoffice', 'woocommerce-german-market' ); ?>" ).appendTo('select[name="action"]');
					jQuery('<option>').val( 'gm_lexoffice_bulk_transmission' ).text("<?php _e( 'Transmit to lexoffice', 'woocommerce-german-market' ); ?>" ).appendTo('select[name="action2"]');
					
				});
				</script>
				<?php
			}

		}

		/**
		* do bulk action
		*
		* @access public
		* @static 
		* @hook load-edit.php
		* @return void
		*/
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

			if ( $action == 'gm_lexoffice_bulk_transmission' ) {

				foreach ( $post_ids as $post_id ) {

					$is_scheduled = get_post_meta( $post_id, '_lexoffice_woocomerce_scheduled_for_transmission', true );

					$order = wc_get_order( $post_id );

					if ( ! apply_filters( 'woocommerce_de_lexoffice_force_transmission_even_if_not_completed', false ) ) {

						if ( $order->get_status() != 'completed' ) {
							continue;
						}

					}

					if ( empty( $is_scheduled ) ) {

						WC()->queue()->add( 'german_market_lexoffice_bulk_transmission', array( 'order_id' => $post_id ), 'german_market_lexoffice' );
						update_post_meta( $post_id, '_lexoffice_woocomerce_scheduled_for_transmission', 'yes' );

					}

				}

			}

		}

		/**
		* transmit one order to lexoffice via bulk
		*
		* @access public
		* @static 
		* @hook german_market_lexoffice_bulk_transmission
		* @param Integer $order_id
		* @return void
		*/
		public static function transmit_one_order_via_bulk( $order_id ) {

			$order = wc_get_order( $order_id );

			if ( ! apply_filters( 'woocommerce_de_lexoffice_force_transmission_even_if_not_completed', false ) ) {

				if ( $order->get_status() != 'completed' ) {
					return;
				}

			}
			
			$response = lexoffice_woocomerce_api_send_voucher( $order, false );

			delete_post_meta( $order_id, '_lexoffice_woocomerce_scheduled_for_transmission' );

		}

		/**
		* transmit one refund to lexoffice via bulk
		*
		* @access public
		* @static 
		* @hook german_market_lexoffice_bulk_transmission_refund
		* @param Integer $refund_id
		* @return void
		*/
		public static function transmit_one_refund_via_bulk( $refund_id ) {

			$refund = wc_get_order( $refund_id );
			$response = lexoffice_woocommerce_api_send_refund( $refund, false );
			delete_post_meta( $refund_id, '_lexoffice_woocomerce_scheduled_for_transmission' );

		}

	}

	$lexoffice_bulk_transmission = new Bulk_Transmission_lexoffice();

}
