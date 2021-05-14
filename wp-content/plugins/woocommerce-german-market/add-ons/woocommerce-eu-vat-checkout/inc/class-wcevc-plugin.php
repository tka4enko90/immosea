<?php

/**
 * Class WCEVC_Plugin
 */
class WCEVC_Plugin {

	/**
	 * @var null|WCEVC_Plugin
	 */
	private static $instance = NULL;

	/**
	 * @return WCEVC_Plugin
	 */
	public static function get_instance() {

		if ( self::$instance === NULL ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Private clone method to prevent cloning of the instance of the
	 * *Singleton* instance.
	 *
	 * @return void
	 */
	private function __clone() {
	}

	/**
	 * @return  WCEVC_Plugin
	 */
	private function __construct() { 

	}

	/**
	 * Start the plugin on plugins_loaded hook.
	 *
	 * @return  void
	 */
	public function run() {

		$config = wcevc_get_plugin_data();

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_style' ) );

		include_once( 'class-wcevc-settings.php' );
		$settings = WCEVC_Settings::get_instance();
		add_filter( 'woocommerce_get_settings_tax', array( $settings, 'add_plugin_setting_to_tax_setting_page' ), 10, 1 );

		$option_wcvmfp_enabled = get_option( 'wcevc_enabled_wgm', 'off' );
		if ( $option_wcvmfp_enabled === 'off' ) {
			return;
		}

		if ( get_option( 'wcevc_show_admin_notices', 'on' ) == 'on' ) {

			require_once( 'class-wcevc-requirements.php' );
			//$requirements = WCEVC_Requirements::get_instance();

			// admin notice - woocommerce deactivated
			//add_action( 'admin_notices',                    array( $requirements, 'print_woocommerce_deactivated_admin_notice' ) );
			// admin notice - tax based on on update options
			//add_action( 'woocommerce_update_options_tax',   array( $requirements, 'print_tax_based_on_admin_notice_on_update' ), 10, 2 );
			//add_action( 'admin_notices',                    array( $requirements, 'print_tax_based_on_admin_notice' ) );
			// admin notice - calc taxes
			//add_action( 'woocommerce_update_options',       array( $requirements, 'print_calc_taxes_admin_notice_on_update' ), 10, 2 );
			//add_action( 'admin_notices',                    array( $requirements, 'print_calc_taxes_admin_notice' ) );
		}

		require_once( 'class-wcevc-calculations.php' );
		$calculations = WCEVC_Calculations::get_instance();
		add_filter( 'woocommerce_product_get_price',  array( $calculations, 'get_price_for_downloadable_products' ), 10, 2 );
		add_filter( 'woocommerce_product_variation_get_price',  array( $calculations, 'get_price_for_downloadable_products' ), 10, 2 );
		
		require_once( 'class-wcevc-taxDisplay.php' );
		$tax_display = WCEVC_TaxDisplay::get_instance();
		add_filter( 'wgm_tax_text', array( $tax_display, 'print_tax_string_without_tax_rate' ), 10, 5 );
	}

	/**
	 * Install callback with check, if WooCommerce is installed.
	 *
	 * @return  void
	 */
	public function activate() {

		require_once( 'class-wcevc-requirements.php' );
		$requirements = WCEVC_Requirements::get_instance();
		$requirements->set_tax_based_on_admin_notice_state();

		require_once( 'class-wcevc-settings.php' );
		$settings = WCEVC_Settings::get_instance();
		$settings->add_default_options();

		// Set default WooCommerce Options
		update_option( 'woocommerce_calc_taxes', 'yes' );
		update_option( 'woocommerce_tax_display_cart', 'incl' );
		update_option( 'woocommerce_tax_display_shop', 'incl' );
	}

	/**
	 * Register admin stylesheet. Enqueue via template.
	 *
	 * @wp-hook admin_enqueue_scripts
	 *
	 * @return  void
	 */
	public function enqueue_admin_style() {

		$config = wcevc_get_plugin_data();
		wp_register_style(
			'wcevc-admin-css',
			$config[ 'css_url' ] . 'admin' . $config[ 'script_suffix' ] . '.css',
			'wp-admin-css',
			$config[ 'version' ]
		);
	}

}
