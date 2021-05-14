<?php
/**
 * Plugin Name:     WooCommerce EU VAT Checkout
 * Description:     From 1 January 2015, digital supplies in B2C transactions to EU countries need to apply taxation according to the VAT rate applicable in the consumer’s billing country. This plugin will fixate product prices you have set in your WooCommerce online store. It will display prices in your shop according to your WooCommerce settings. During  checkout it will then dynamically calculate taxes included in line item prices and totals according to the shipping country entered by your customer and the tax rates you have set for that particular country.
 * Author:          MarketPress
 */

if ( ! function_exists( 'add_action' ) ) {
	return;
}

// don't load add-on if plug is activated
if ( ! function_exists( 'wcevc_setup' ) ) {


	require_once( 'inc' . DIRECTORY_SEPARATOR . 'class-wcevc-plugin.php' );

	/**
	 * Setup function for our plugin.
	 *
	 * @wp-hook plugins_loaded
	 *
	 * @return  void
	 */
	function wcevc_setup() {
		$plugin = WCEVC_Plugin::get_instance();
		$plugin->run();
	}

	/**
	 * Callback for activating the plugin.
	 *
	 * @return  void
	 */
	function wcevc_activate() {
		$plugin = WCEVC_Plugin::get_instance();
		$plugin->activate();
	}

	/**
	 * Get the plugin settings.
	 *
	 * @return  array $config
	 */
	function wcevc_get_plugin_data() {

		$config = wp_cache_get( 'wcevc', 'config' );

		if ( ! ! $config && is_array( $config ) ) {
			return $config;
		}

		$file = __FILE__;

		$default_headers = array(
			'plugin_name'      => 'Plugin Name',
			'plugin_uri'       => 'Plugin URI',
			'description'      => 'Description',
			'author'           => 'Author',
			'version'          => 'Version',
			'author_uri'       => 'Author URI',
			'textdomain'       => 'Textdomain',
			'text_domain_path' => 'Domain Path',
		);
		$config          = get_file_data( $file, $default_headers );

		$config[ 'plugin_dir_path' ]  = plugin_dir_path( $file );
		$config[ 'plugin_file_path' ] = $file;
		$config[ 'plugin_base_name' ] = plugin_basename( $file );
		$config[ 'plugin_url' ]       = plugins_url( '/', $file );

		// assets
		$config[ 'css_url' ] = $config[ 'plugin_url' ] . 'assets/css/';

		// modes
		$config[ 'debug_mode' ]        = defined( 'WP_DEBUG' ) && WP_DEBUG;
		$config[ 'script_debug_mode' ] = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
		$config[ 'script_suffix' ]     = $config[ 'script_debug_mode' ] ? '' : '.min';

		wp_cache_set( 'wcevc', $config, 'config' );

		return $config;
	}


	if ( ! function_exists( 'pre' ) ) {
		/**
		 * Debugging-Helper to print some args.
		 *
		 * @return void
		 */
		function pre( ) {
			$args = func_get_args();
			foreach ( $args as $arg ) {
				echo "<pre>" . print_r( $arg, TRUE ) . "</pre>";
			}
		}
	}

	wcevc_setup();
	register_activation_hook( __FILE__, 'wcevc_activate' );

}

