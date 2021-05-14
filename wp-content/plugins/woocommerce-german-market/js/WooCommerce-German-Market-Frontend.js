if ( german_market_jquery_no_conflict.val == 'yes' ) {
	jQuery.noConflict();
}

(
	function( $ ) {

		var woocommerce_de = {

			init: function() {
				//this.setupAjax();
				this.remove_totals();
				this.register_payment_update();
				this.on_update_variation();
				this.sepa_direct_debit();
				this.second_checkout_place_order();
				this.deactivate_ship_to_different_address_purchase_on_account();
			},

			deactivate_ship_to_different_address_purchase_on_account: function() {
				
				$( 'body' ).on( 'update_checkout', function() {

					if ( $( '#deactivate_ship_to_different_address_if_purchase_on_account' ).length ) {
						
						if ( ! $( '#payment_method_german_market_purchase_on_account' ).length ) {
							return;
						}

						var is_purchase_on_account = $( '#payment_method_german_market_purchase_on_account' ).prop( 'checked' );

						if ( is_purchase_on_account ) {
						
							if ( $( '#deactivate_ship_to_different_address_if_purchase_on_account' ).val() == '1' || $( '#deactivate_ship_to_different_address_if_purchase_on_account' ).val() == 'yes' ) {

								if ( $( '#ship-to-different-address-checkbox' ).length ) {

									if ( $( '#ship-to-different-address-checkbox' ).prop( "checked" ) === true ) {

										if ( $( ship_different_address.before_element ).length ) {
											
											if ( ! $( '#german-market-puchase-on-account-message' ).length ) {
												$( ship_different_address.before_element ).before(  ship_different_address.message  );
											}
											
											$( '#german-market-puchase-on-account-message' ).show();
										}
									}

									$( '#ship-to-different-address-checkbox' ).prop( "checked", false );
								}
								
								if ( $( '.shipping_address' ).length ) {
									$( '.shipping_address' ).hide();
								}

								if ( $( '.woocommerce-shipping-fields' ).length ) {
									$( '.woocommerce-shipping-fields' ).hide();
								}

							}

						} else {

							if ( $( '.woocommerce-shipping-fields' ).length ) {
								$( '.woocommerce-shipping-fields' ).show();
							}

							$( '#german-market-puchase-on-account-message' ).hide();

						}
					}
					
				});  	

			},

			second_checkout_place_order: function() {

				$( ':submit.wgm-place-order' ).click( function(){
					
					if ( $( '.wgm-place-order-disabled' ).length ) {
						$( '.wgm-place-order-disabled' ).show();
					}

				});

			},

			// not in use any more, but still exists for compatibility check reasons
			setupAjax: function() {
				if ( typeof wgm_wpml_ajax_language !== 'undefined' ) {
					$.ajaxSetup( { data: { 'lang': wgm_wpml_ajax_language } } );
				}
			},

			remove_totals: function() {

				if ( woocommerce_remove_updated_totals.val == 1 ) {
					$( '.woocommerce_message' ).remove();
				}
			},

			register_payment_update: function() {
				
				if ( woocommerce_payment_update.val == 1 ) {
					$( document.body ).on( 'change', 'input[name="payment_method"]', function() {
						$( 'body' ).trigger( 'update_checkout' );
					} );
				}
				
			},

			on_update_variation: function() {

				if ( german_market_price_variable_products.val == 'gm_default' ) {
					
					var product = $( '.single-product' ), price = $( '.legacy-itemprop-offers' );
					product.on( 'found_variation', '.variations_form', function() {
						price.slideUp();
						// Extra Theme Element Price
						if ( german_market_price_variable_theme_extra_element.val != 'none' ) {
							jQuery( german_market_price_variable_theme_extra_element.val ).slideUp();
						}
					} );

					product.on( 'reset_data', '.variations_form', function() {
						price.slideDown();
						// Extra Theme Element Price
						if ( german_market_price_variable_theme_extra_element.val != 'none' ) {
							jQuery( german_market_price_variable_theme_extra_element.val ).slideDown();
						}
					} );

				} else if ( german_market_price_variable_products.val == 'gm_sepcial' ) {

					var product = $( '.single-product' );

					product.on( 'found_variation', '.variations_form', function() {

						var variation_price_helper = '<div id="german-market-variation-price"></div>';
						
						var price = jQuery( '.woocommerce-variation-price.woocommerce-variation-price' ).html();

						if ( $( '.woocommerce-variation-availability' ).length ) {
							price += $( '.woocommerce-variation-availability' ).html();
						}

						if ( $( '.woocommerce-variation-description' ).length ) {
							price += $( '.woocommerce-variation-description' ).html();
						}

						jQuery( '.woocommerce-variation.single_variation' ).hide();
						jQuery( '.woocommerce-variation-price' ).hide();
						jQuery( '.legacy-itemprop-offers' ).hide();
						
						// Extra Theme Element Price
						if ( german_market_price_variable_theme_extra_element.val != 'none' ) {
							jQuery( german_market_price_variable_theme_extra_element.val ).hide();
						}

						// Elementor
						if ( jQuery( '.elementor-widget-woocommerce-product-price .price' ).length ) {
							jQuery( '.elementor-widget-woocommerce-product-price .price' ).hide();
						}

						// DT WooCommerce Page Builder (WPBakery Page Builder)
						if ( jQuery( '.dtwpb-price.price' ).length ) {
							if ( ! jQuery( '#german-market-variation-price' ).length ) {
								jQuery( variation_price_helper ).insertAfter( '.dtwpb-price.price' );
							}
							jQuery( '.dtwpb-price.price' ).hide();
							if ( jQuery( '.gm-wp_bakery_woocommerce_get_price_html' ).length ) {
								jQuery( '.gm-wp_bakery_woocommerce_get_price_html' ).hide();
							}
							
						}

						// Divi Page Builder
						if ( jQuery( '.et_pb_wc_price' ).length ) {
							if ( ! jQuery( '#german-market-variation-price' ).length ) {
								jQuery( variation_price_helper ).insertAfter( '.et_pb_wc_price' );
							}
							jQuery( '.et_pb_wc_price' ).hide();
						}

						// German Market and Page Builder Compatibility
						if ( ! jQuery( '#german-market-variation-price' ).length ) {
							jQuery( variation_price_helper ).insertAfter( '.legacy-itemprop-offers' );
						} else {
							jQuery( '#german-market-variation-price' ).show();
						}

						jQuery( '#german-market-variation-price' ).html( price );

					} );

					product.on( 'reset_data', '.variations_form', function() {
						
						// Elementor
						if ( jQuery( '.elementor-widget-woocommerce-product-price .price' ).length ) {
							jQuery( '.elementor-widget-woocommerce-product-price .price' ).show();
						}

						// DT WooCommerce Page Builder (WPBakery Page Builder)
						if ( jQuery( '.dtwpb-price.price' ).length ) {
							jQuery( '.dtwpb-price.price' ).show();
							if ( jQuery( '.gm-wp_bakery_woocommerce_get_price_html' ).length ) {
								jQuery( '.gm-wp_bakery_woocommerce_get_price_html' ).show();
							}
						}

						// Divi Page Builder
						if ( jQuery( '.et_pb_wc_price' ).length ) {
							jQuery( '.et_pb_wc_price' ).show();
						}

						jQuery( '.legacy-itemprop-offers' ).show();
						jQuery( '#german-market-variation-price' ).hide();
						
						// Extra Theme Element Price
						if ( german_market_price_variable_theme_extra_element.val != 'none' ) {
							jQuery( german_market_price_variable_theme_extra_element.val ).show();
						}


					} );

				}

			},

			sepa_direct_debit_show_preview: function() {

				var data = {
					'holder' 	: $( '[name="german-market-sepa-holder"]' ).val(),
					'iban' 		: $( '[name="german-market-sepa-iban"]' ).val(),
					'bic' 		: $( '[name="german-market-sepa-bic"]' ).val(),
					'street' 	: $( '[name="billing_address_1"]' ).val(),
					'zip'		: $( '[name="billing_postcode"]' ).val(),
					'city'		: $( '[name="billing_city"]' ).val(),
					'country'	: $( '[name="billing_country"]' ).val(),
				};

				var show = true;
				var is_box_shown = jQuery( '#german-market-sepa-checkout-fields' ).is(":visible");

				if ( is_box_shown ) {
					for ( key in data ) {

						if ( key == 'bic' || key == 'iban' || key == 'holder' ) {
							if ( $( '[name="german-market-sepa-' + key + '"]' ).hasClass( 'gm-required-no' ) ) {
								continue;
							}
						}

						if ( data[ key ] !== undefined && data[ key ].trim() == '' ) {
							show = false;
							break;
						}
					}
				} else {
					show = false;
				}

				return show;

			},

			sepa_direct_debit_show_preview_do: function() {

				var do_it = woocommerce_de.sepa_direct_debit_show_preview();

				if ( do_it ) {

					$( '.gm-sepa-direct-debit-second-checkout-disabled' ).show();
					$( '.gm-sepa-direct-debit-order-pay' ).show();

				} else {

					$( '.gm-sepa-direct-debit-second-checkout-disabled' ).hide();
					$( '.gm-sepa-direct-debit-order-pay' ).hide();
					$( '#gm-sepa-mandate-preview-text' ).slideUp();

				}

			},

			sepa_direct_debit: function() {

				$( document.body ).on( 'click', '#gm-sepa-mandate-preview', function( e ){

					e.preventDefault();

					var data = {
						'action'	: 'gm_sepa_direct_debit_mandate_preview',
						'holder' 	: $( '[name="german-market-sepa-holder"]' ).val(),
						'iban' 		: $( '[name="german-market-sepa-iban"]' ).val(),
						'bic' 		: $( '[name="german-market-sepa-bic"]' ).val(),
						'street' 	: $( '[name="billing_address_1"]' ).val(),
						'zip'		: $( '[name="billing_postcode"]' ).val(),
						'city'		: $( '[name="billing_city"]' ).val(),
						'country'	: $( '[name="billing_country"]' ).val(),
						'nonce'		: sepa_ajax_object.nonce
					};

					jQuery.post( sepa_ajax_object.ajax_url, data, function( response ) {
						$( '#gm-sepa-mandate-preview-text' ).html( response );
						$( '#gm-sepa-mandate-preview-text' ).slideDown();

						$( '#gm-sepa-mandate-preview-text .close' ).click( function(){
							$( '#gm-sepa-mandate-preview-text' ).slideUp();
						});
					});

				});


				$( '#gm-sepa-mandate-preview' ).ready( function(){

					if ( ! $( '#gm-sepa-mandate-preview' ).length ) {
						return;
					}

					var fields = {
						0 : '[name="german-market-sepa-holder"]',
						1 : '[name="german-market-sepa-iban"]' ,
						2 : '[name="german-market-sepa-bic"]',
						3 : '[name="billing_address_1"]',
						4 : '[name="billing_postcode"]',
						5 : '[name="billing_city"]',
						6 : '[name="billing_country"]',
					};

					for ( key in fields ) {
						$( document.body ).on( 'change keyup', fields[ key ], woocommerce_de.sepa_direct_debit_show_preview_do );
					}
				
				});

				$( document.body ).on( 'change', 'input[name="payment_method"]', function() {

					if ( $( this ).val() == 'german_market_sepa_direct_debit' ) {
						$( '.gm-sepa-direct-debit-second-checkout-disabled' ).show();
					} else {
						$( '.gm-sepa-direct-debit-second-checkout-disabled' ).hide();
					}
				});

				$( '.gm-sepa-direct-debit-second-checkout-disabled' ).ready( function() {
					if ( $( '#payment_method_german_market_sepa_direct_debit' ).is(':checked' ) ) {
						$( '.gm-sepa-direct-debit-second-checkout-disabled' ).show();
					}
				});

				$( document.body ).on( 'change', 'input[name="shipping_method[0]"]', function() {

					if ( $( '#p-shipping-service-provider' ).length ) {

						var shipping_method = $( this ).val();
						var is_local_pickup = shipping_method.includes( 'local_pickup' );

						if ( ! is_local_pickup ) {

							$( '#p-shipping-service-provider' ).show();

						} else {

							$( '#p-shipping-service-provider' ).hide();

						}

					}
					
				});

			}
		};

		$( document ).ready( function( $ ) {
			woocommerce_de.init();
		} );

	}
)( jQuery );