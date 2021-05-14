<?php

/**
 * Class WCEVC_TaxDisplay
 */
class WCEVC_TaxDisplay {

	/**
	 * @var null|WCEVC_TaxDisplay
	 */
	private static $instance = NULL;

	/**
	 * Private clone method to prevent cloning of the instance of the
	 * *Singleton* instance.
	 *
	 * @return void
	 */
	private function __clone() {
	}

	/**
	 * @return WCEVC_TaxDisplay
	 */
	public static function get_instance() {

		if ( self::$instance === NULL ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @return  WCEVC_TaxDisplay
	 */
	private function __construct() {
	}

	/**
	 * Returns the tax-string without tax-rate within archive- and single-template.
	 *
	 * @wp-hook wgm_tax_text
	 *
	 * @param   string     $text
	 * @param   WC_Product $product
	 * @param   string     $include_string
	 * @param   array      $rate
	 *
	 * @return  string $text
	 */
	public function print_tax_string_without_tax_rate( $text, WC_Product $product, $include_string, $rate, $tax_display ) {

		if ( ! $product->is_downloadable() ) {
			return $text;
		}

		if ( $tax_display == 'incl' ) {
			
			$text = sprintf(
			/* translators: %1$s: tax rate label */
				__( 'Includes %s', 'woocommerce-german-market' ),
				apply_filters( 'wgm_wcevc_digital_product_tax_label', $rate[ 'label' ], $rate, $product )
			);

		} else {

			$text = sprintf(
			/* translators: %1$s: tax rate label */
				__( 'Plus %s', 'woocommerce-german-market' ),
				apply_filters( 'wgm_wcevc_digital_product_tax_label', $rate[ 'label' ], $rate, $product )
			);

		}

		return $text;
	}

}
