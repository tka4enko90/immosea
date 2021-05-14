<?php

/**
 * Class WCEVC_Requirements
 */
class WCEVC_Requirements {

	/**
	 * Contains all notices mapped by notice_type
	 * @var array
	 */
	private $notices = array();

	/**
	 * @var null|WCEVC_Requirements
	 */
	private static $instance = NULL;

	/**
	 * Private clone method to prevent cloning of the instance of the
	 * *Singleton* instance.
	 *
	 * @return void
	 */
	private function __clone() { }

	/**
	 * @return WCEVC_Requirements
	 */
	public static function get_instance() {

		if ( self::$instance === NULL ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @return  WCEVC_Requirements
	 */
	private function __construct() {
		/*
		$this->notices  = array();
		$plugin_data    = wcevc_get_plugin_data();
		$url            = admin_url( 'admin.php?page=wc-settings&tab=tax' );
		$plugin_name    = translate( $plugin_data[ 'plugin_name' ], 'woocommerce-german-market' );

		// option "woocommerce_calc_taxes" is not "yes"
		$this->notices[ 'calc_taxes' ] = array(
			'message' => sprintf(
				__( '<strong>%1$s</strong> has been deactivated automatically, because tax calculation is disabled in your WooCommerce settings. In order to activate %1$s, please <a href="%2$s" target="_top">enable tax calculation in WooCommerce</a>.', 'woocommerce-german-market' ),
				$plugin_name,
				$url
			),
			'show_dismiss' => false
		);

		// option "woocommerce_tax_based_on" is not "billing"
		$this->notices[ 'tax_based_on' ] = array(
			'message' => sprintf(
				__( 'In order for <strong>%1$s</strong> to function correctly, please change your <a href="%2$s">WooCommerce Tax Settings</a> to <strong>calculate tax based on customer billing address</strong>.', 'woocommerce-german-market' ),
				$plugin_name,
				$url
			),
			'show_dismiss' => false
		);

		// WooCommerce is not activated
		$this->notices[ 'wc_not_active' ] = array(
			'message' => sprintf(
				__( '<strong>WooCommerce is not active.</strong> In order to use %1$s, please activate WooCoomerce first.', 'woocommerce-german-market' ),
				$plugin_name
			),
			'show_dismiss' => false
		);
		*/
		$this->maybe_dismiss_admin_notice();

	}

	/**
	 * Shows admin notice on update for "woocommerce_calc_taxes" !== "yes".
	 *
	 * @wp-hook woocommerce_update_options
	 *
	 * @return  void
	 */
	public function print_calc_taxes_admin_notice_on_update() {

		$option_calc_taxes = get_option( 'woocommerce_calc_taxes' );
		if ( $option_calc_taxes === 'yes' ) {
			return;
		}

		$this->show_admin_notice( 'calc_taxes' );
	}

	/**
	 * Shows admin notice  for "woocommerce_calc_taxes" !== "yes".
	 *
	 * @wp-hook admin_notices
	 *
	 * @return void
	 */
	public function print_calc_taxes_admin_notice() {

		if ( ! $this->is_woocommerce_activated() ) {
			return;
		}

		if ( ! $this->is_calc_taxes_admin_notice_visible() ) {
			return;
		}

		$this->show_admin_notice( 'calc_taxes' );
	}

	/**
	 * Shows admin notice on update for "woocommerce_tax_based_on" !== "billing".
	 *
	 * @wp-hook woocommerce_update_options_tax
	 *
	 * @return  void
	 */
	public function print_tax_based_on_admin_notice_on_update() {

		$dismiss = $this->set_tax_based_on_admin_notice_state();
		if ( $dismiss !== 'false' ) {
			return;
		}

		$this->show_admin_notice( 'tax_based_on' );
	}

	/**
	 * Shows admin notice for "woocommerce_tax_based_on" !== "billing".
	 *
	 * @wp-hook admin_notices
	 *
	 * @return  void
	 */
	public function print_tax_based_on_admin_notice() {

		if ( ! $this->is_woocommerce_activated() ) {
			return;
		}

		if ( ! $this->is_tax_based_on_admin_notice_visible() ) {
			return;
		}

		$this->show_admin_notice( 'tax_based_on' );
	}

	/**
	 * Shows admin notice, if WooCommerce is deactivated.
	 *
	 * @wp-hook admin_notices
	 *
	 * @return void
	 */
	public function print_woocommerce_deactivated_admin_notice() {

		if ( $this->is_woocommerce_activated() ) {
			return;
		}

		$this->show_admin_notice( 'wc_not_active' );
	}

	/**
	 * Internal function to print the admin_notice-template.
	 *
	 * @param   string $type
	 *
	 * @return  void
	 */
	private function show_admin_notice( $type ) {

		if ( ! array_key_exists( $type, $this->notices ) ) {
			return;
		}

		// used in template
		$message        = $this->notices[ $type ][ 'message' ];
		$show_dismiss   = $this->notices[ $type ][ 'show_dismiss' ];

		wp_enqueue_style( 'wcevc-admin-css' );
		$config = wcevc_get_plugin_data();
		include_once( $config[ 'plugin_dir_path' ] . 'parts/admin-notice.php' );
	}

	/**
	 * Internal helper function to detect, if the admin_notices for "calc_taxes" !== "yes" is visible.
	 *
	 * @return bool true|false
	 */
	private function is_calc_taxes_admin_notice_visible() {
		// on update (POST-Request), don't show the dialog here, because WC saves its options after the admin_notice-Hook.
		$page = isset( $_GET[ 'page' ] ) ? $_GET[ 'page' ] : '';
		if ( $page === 'wc-settings' && $_SERVER[ 'REQUEST_METHOD' ] === 'POST' ) {
			return false;
		}

		$option_calc_taxes  = get_option( 'woocommerce_calc_taxes' );
		if ( $option_calc_taxes === 'yes' ) {
			return false;
		}

		return true;
	}

	/**
	 * Internal helper function to detect, if the admin_notices for "tax_based_on" !== "billing" is visible.
	 *
	 * @return bool true|false
	 */
	private function is_tax_based_on_admin_notice_visible() {
		// on update (POST-Request), don't show the dialog here, because WC saves its options after the admin_notice-Hook.
		$current_tab = isset( $_GET[ 'tab' ] ) ? sanitize_title( $_GET[ 'tab' ] ) : '';
		if ( $current_tab === 'tax' && $_SERVER[ 'REQUEST_METHOD' ] === 'POST' ) {
			return false;
		}

		$option_dismiss_notice = get_option( 'wcevc_dismiss_tax_based_on_notice_wgm', '' );
		if ( $option_dismiss_notice !== 'false' ) {
			return false;
		}

		return true;
	}

	/**
	 * Set the state of admin notice for "woocommerce_tax_based_on".
	 *
	 * @return  string $option_notice
	 */
	public function set_tax_based_on_admin_notice_state() {
		$option_notice          = get_option( 'wcevc_dismiss_tax_based_on_notice_wgm' );
		$option_tax_based_on    = get_option( 'woocommerce_tax_based_on', '' );
		if ( ! $option_notice || $option_tax_based_on !== 'billing' ) {
			$option_notice = 'false';
		} else {
			$option_notice = 'true';
		}

		update_option( 'wcevc_dismiss_tax_based_on_notice_wgm', $option_notice );

		return $option_notice;
	}

	/**
	 * Set the dismiss state of admin notice.
	 *
	 * @return void
	 */
	private function maybe_dismiss_admin_notice() {

		if ( ! isset( $_GET[ 'wcevc_dismiss_notice' ] ) || ! isset( $_GET[ '_wcevc_nonce' ] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( $_GET[ '_wcevc_nonce' ], 'wcevc' ) ) {
			return;
		}

		$notice_key = $_GET[ 'wcevc_dismiss_notice' ];
		if ( ! array_key_exists( $notice_key, $this->notices ) || ! $this->notices[ $notice_key ][ 'show_dismiss' ] ) {
			return;
		}

		$option_name = 'wcevc_dismiss_' . $notice_key . '_notice';
		update_option( $option_name, 'true' );
	}

	/**
	 * Helper-Function to detect, if WooCommerce is activated.
	 *
	 * @return bool true|false
	 */
	public function is_woocommerce_activated(){
		$plugins = get_plugins();

		foreach ( $plugins as $path => $plugin ) {

			$plugin_name = strtolower( $plugin[ 'Name' ] );

			if ( $plugin_name !== 'woocommerce' ) {
				continue;
			}

			if ( ! is_plugin_active( $path ) && ! is_plugin_active_for_network( $path ) ) {
				return false;
			}
		}

		return true;
	}

}