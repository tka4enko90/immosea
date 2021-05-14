<?php

/**
 * Class WCEVC_Calculations
 */
class WCEVC_Calculations {

	/**
	 * @var null|WCEVC_Calculations
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
	 * @return WCEVC_Calculations
	 */
	public static function get_instance() {

		if ( self::$instance === NULL ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @return  WCEVC_Calculations
	 */
	private function __construct() { }

	/**
	 * Callback to calculate correct price for downloadable products for "billing address tax rates" !== "shop base tax rates".
	 *
	 * @wp-hook woocommerce_get_price_including_tax
	 *
	 * @param   int $price
	 * @param   int $qty
	 * @param   WC_Product $product
	 *
	 * @return  int $price
	 */
	public function get_price_for_downloadable_products( $price, $product ) {
		
		if ( apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) === FALSE ) {
			return $price;
		}

		$price_include_taxes = get_option( 'woocommerce_prices_include_tax' );
		if ( $price_include_taxes === 'no' ) {
			return $price;
		}

		if ( $this->is_product_vatmoss_eligible( $product ) ) {
			$price = $this->generate_new_price( $product, $price );
		}

		return $price;

	}

	/**
	 * Returns if the given cart_items contain at least one product for vatmoss-calcualations.
	 *
	 * @param array $cart_items		the cart-items of $cart->get_cart().
	 *
	 * @return boolean true|false   true, if the cart has at least one vatmoss-product, false if no vatmoss-product was found.
	 */
	public function has_vatmoss_products( $cart_items ) {
		$has_vatmoss_products = false;

		foreach ( $cart_items as $item ) {
			$product = $item[ 'data' ];
			if ( $this->is_product_vatmoss_eligible( $product ) ) {
				$has_vatmoss_products = true;
				break;
			}
		}

		return $has_vatmoss_products;
	}

	/**
	 * Generate the new line item with calculated prices and taxes based on brutto-price.
	 *
	 * @param   array $item
	 * @param   WC_Cart $cart
	 *
	 * @return  array $line_data    the new $item with correct prices
	 */
private function generate_new_price ( $product, $price ) {

		$base_tax_rates_array = WC_Tax::get_base_tax_rates( $product->get_tax_class( 'unfiltered' ) );
		$base_tax_rates = array();	
		
		if ( ! empty( $base_tax_rates_array ) ) {
			$base_tax_rates = array_shift( $base_tax_rates_array );
		} 

		if ( isset( $base_tax_rates[ 'rate' ] ) && ( $base_tax_rates[ 'rate' ] > 0.0 ) ) {
			$price = $price * ( ( $base_tax_rates[ 'rate' ] + 100 ) / 100 );
		}

		$new_tax_rates_array  = array();
		if ( is_user_logged_in() ) {
			
			if ( WC()->customer && ( ! is_null( WC()->customer ) ) ) {

				$args = array(
					'tax_class' => $product->get_tax_class(),
					'country'   => WC()->customer->get_billing_country()
				);
				$new_tax_rates_array = WC_Tax::find_rates( $args );
			
			} else {

				$new_tax_rates_array = WC_Tax::get_rates( $product->get_tax_class() );

			}

		} else {
			$new_tax_rates_array = WC_Tax::get_rates( $product->get_tax_class() );
		}

		if ( ! empty( $new_tax_rates_array ) ) {
			$new_tax_rates = array_shift( $new_tax_rates_array );
		}

		if ( isset( $new_tax_rates[ 'rate' ] ) ) {
			$price = $price / ( ( $new_tax_rates[ 'rate' ] + 100 ) / 100 );
		}

		return $price;

	}

	/**
	 *  Check if ...
	 *      - the product is downloadable
	 *      - the selected customer country is inside the EU.
	 *      - the "shop base tax rate" !== "product tax rate"
	 *
	 * This method requires the option 'wcevc_enabled_wgm' set to 'downloadable'.
	 *
	 * @param WC_Product $product
	 *
	 * @return bool
	 */
	public static function is_product_vatmoss_eligible( $product ) {

		if ( ! $product->is_downloadable() ) {
			return false;
		}

		$wc = WC();
		if ( empty( $wc->customer ) ) {
			return false;
		}

		$eu_countries       = $wc->countries->get_european_union_countries();
		$customer_country   = $wc->customer->get_billing_country();
		if ( ! in_array( $customer_country, $eu_countries ) ) {
			return false;
		}

		$tax_rates           = WC_Tax::get_rates( $product->get_tax_class() );
		$shop_base_tax_rates = WC_Tax::get_base_tax_rates( $product->get_tax_class() );
		if ( $tax_rates === $shop_base_tax_rates ) {
			return false;
		}

		return true;
	}

}