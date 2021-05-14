<?php

/**
 * Class WCEVC_Settings
 */
class WCEVC_Settings {

	/**
	 * @var array
	 */
	private $options = array();

	/**
	 * @var null|WCEVC_Settings
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
	 * @return WCEVC_Settings
	 */
	public static function get_instance() {

		if ( self::$instance === NULL ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @return  WCEVC_Settings
	 */
	private function __construct() {

		$plugin_data    = wcevc_get_plugin_data();

		// disable/enable plugin for downloadable products
		$this->options[]= array(
			'title'   => translate( $plugin_data[ 'plugin_name' ], 'woocommerce-german-market' ),
			'id'      => 'wcevc_enabled_wgm',
			'desc'    => '<br />' . __( 'Fixate gross prices and re-calculate taxes during checkout according to tax rates set for billing country.', 'woocommerce-german-market' ),
			'default' => 'downloadable',
			'type'    => 'select',
			'options' => array(
				'off'           => __( 'Disable', 'woocommerce-german-market' ),
				'downloadable'  => __( 'Enable for downloadable products', 'woocommerce-german-market' ),
			)
		);

	}

	/**
	 * Add Option to WooCommerce tax options page.
	 *
	 * @wp-hook woocommerce_get_settings_tax
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	public function add_plugin_setting_to_tax_setting_page( array $options ) {

		$options = $this->maybe_remove_sectionend_from_options( $options );

		foreach ( $this->options as $option ) {
			$options[ ] = $option;
		}

		// add section to options
		$options[ ] = array(
			'type' => 'sectionend',
			'id'   => 'tax_options'
		);

		return $options;
	}

	/**
	 * Internal function to remove the "sectionend" from options
	 *
	 * @param   array $options
	 *
	 * @return  array array
	 */
	private function maybe_remove_sectionend_from_options( array $options ) {
		
		if ( ! empty( $options ) ) {
			$options_count = key( array_slice( $options, -1, 1, TRUE ) );
			$last_option   = $options[ $options_count ];

			if ( $last_option[ 'type' ] === 'sectionend' ) {
				array_pop( $options );
			}
		}

		return $options;
	}

	/**
	 * Set the default options of our Plugins on activation
	 *
	 * @return void
	 */
	public function add_default_options() {
		foreach ( $this->options as $option ) {
			add_option( $option[ 'id' ], $option[ 'default' ] );
		}
	}

}