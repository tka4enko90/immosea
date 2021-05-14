<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCREAPDF_Backend_Activation' ) ) {
	
	/**
	* When plugin is activated
	*
	* @class WCREAPDF_Backend_Activation
	* @version 1.0
	* @category	Class
	*/
	class WCREAPDF_Backend_Activation {
		
		/**
		* when activated
		*
		* @since 0.0.1
		* @access public
		* @static
		* @return void
		*/	
		public static function activation() {
			// create temp diretories if they do not exist
			update_option( 'woocomerce_wcreapdf_wgm_just_activated', true );
		}
		
		/**
		* get admin notices
		*
		* @since 0.0.1
		* @access public
		* @static
		* @return array of strings
		*/	
		public static function get_notices() {
			$notices = array();	
			
			// check woocomerce
			if ( ! self::is_woocommerce_activated() ){
				$notices[] = __( 'The Plugin <strong>WooCommerce</strong> is not active, but it is necessary to use this plugin.', 'woocommerce-german-market' );
			} else { // woocommerce is activated
				// check wc version
				if ( ! version_compare( @$woocommerce->version , '2.2.8', '>=' ) ) {
					__( 'You need at least <strong>WooCommerce</strong> version 2.2.8 to use this plugin.', 'woocommerce-german-market' );
				}	
			}
			
			// check wordpress
			if ( ! version_compare( $GLOBALS[ 'wp_version' ], '4.0', '>=' ) ) {
				$notices[] = __( 'This plugin needs at least Wordpress 4.0.', 'woocommerce-german-market' );
			}
			
			// check php
			if ( version_compare( PHP_VERSION, '5.2.0', '<' ) ) {
				$notices[] = __( 'This plugin needs at least PHP version 5.2.0+.', 'woocommerce-german-market' );
			}
			
			return $notices;
		}
		
		
		/**
		* create cache folders for pdfs and images
		*
		* @since 0.0.1
		* @access public
		* @static
		* @return void
		*/
		public static function create_temp_directories() {
			if ( ! file_exists( untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' ) ) {
				mkdir( untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' );
			}
			
			if ( ! file_exists( untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'woocommerce-return-delivery-pdf' ) ) {
				mkdir( untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'woocommerce-return-delivery-pdf' );
				mkdir( untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'woocommerce-return-delivery-pdf' . DIRECTORY_SEPARATOR . 'pdf' );
				mkdir( untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'woocommerce-return-delivery-pdf' . DIRECTORY_SEPARATOR . 'fonts' );	
			}
		}
		
		/**
		* output admin notices
		*
		* @since 0.0.1
		* @access public
		* @static
		* @hook admin_notices
		* @return void
		*/	
		public static function output_notices() {
			update_option( 'woocomerce_wcreapdf_wgm_just_activated', false );			// save that activation has been
			$notices = self::get_notices();		
			if ( count( $notices ) != 0 ) {											// if there are error notices, echo them
				foreach ( $notices as $notice ) {
					echo '<div class="error"><p>' . $notice . '</p></div>';	
				}
				// if there were error notices, deactivate our plugin
				// we decided, not to deactivate ourself
				//deactivate_plugins( plugin_basename( Woocommerce_Return_Delivery_Pdf::$plugin_filename ) );
			} else {
				// everything fine
				$notice = __( '<strong>Woocomerce Retoure Email Attchment Pdf</strong> has been activated successfull. You can go to the settings page here:', 'woocommerce-german-market' );
				$here = __( 'Settings page', 'woocommerce-german-market' );
				$url  = admin_url() . 'admin.php?page=wc-settings&tab=preferences-wcreapdf';
				echo '<div class="updated"><p>' . $notice . ' <a href="' . $url . '">' . $here . '</a></p></div>';	
			}
		}
		
		/**
		* check if woocommerce is installed and activated
		*
		* @since 0.0.1
		* @access public
		* @static
		* @return boolean
		*/	
		public static function is_woocommerce_activated(){
			$plugins = get_plugins();
			foreach ( $plugins as $path => $plugin ) {
				$plugin_name = strtolower( $plugin[ 'Name' ] );
				if ( $plugin_name === 'woocommerce' ) {
					if ( is_plugin_active( $path ) || is_plugin_active_for_network( $path ) ) {
						return true;
					}
				}
			}
			return false;
		}
	
		
	} // end class
} // end if
?>