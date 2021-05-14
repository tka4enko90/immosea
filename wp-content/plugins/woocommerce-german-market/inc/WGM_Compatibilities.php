<?php

/**
 * Class WGM_Compatibilities
 *
 * German Market Userinterface
 *
 * @author MarketPress
 */
class WGM_Compatibilities {

	/**
	 * @var WGM_Compatibilities
	 * @since v3.1.2
	 */
	private static $instance = null;

	/**
	* Subscriptions WGM_Compatibility Variables
	**/
	private $_new_order_create;
    private $_wp_wc_running_invoice_number;
    private $_wp_wc_running_invoice_number_date;

    public static $theme_compatibilities_path;

	/**
	* Singletone get_instance
	*
	* @static
	* @return WGM_Compatibilities
	*/
	public static function get_instance() {
		if ( self::$instance == NULL) {
			self::$instance = new WGM_Compatibilities();
		}
		return self::$instance;
	}

	/**
	* Singletone constructor
	*
	* @access private
	*/
	private function __construct() {

		// init
		self::$theme_compatibilities_path = Woocommerce_German_Market::$plugin_path . 'inc' . DIRECTORY_SEPARATOR . 'compatibilities' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR;

		// Checkout Strings
		$options = WGM_Helper::get_translatable_options();

		// Due Date
		if ( get_option( 'woocommerce_de_due_date', 'off' ) == 'on' ) {

			add_action( 'admin_init', array( $this, 'due_date' ) );

		}

		/******************************
		// Compatibility with WPML
		******************************/

		if ( function_exists( 'wpml_loaded' ) ) {
			add_filter( 'woocommerce_de_ui_left_menu_items', array( $this, 'add_wpml_menu' ) );
		}

		if ( function_exists( 'icl_register_string' ) && function_exists( 'icl_t' ) && function_exists( 'icl_st_is_registered_string' ) ) {

			add_filter( 'woocommerce_find_rates',												array( $this, 'translate_woocommerce_find_rates' ), 10 );
			add_filter( 'wgm_translate_tax_label',												array( $this, 'translate_tax_label' ) );
			add_filter( 'woocommerce_de_get_deliverytime_label_term',							array( $this, 'wpml_translate_delivery_times' ), 10, 2 );
			add_filter( 'german_market_measuring_unit',											array( $this, 'wpml_translate_measuring_unit' ) );
			add_filter( 'german_market_ppu_co_woocommerce_order_formatted_line_subtotal', 		array( $this, 'wpml_translate_measuring_unit_in_order' ), 10, 3 );
			add_filter( 'add_deliverytime_to_order_item', 										array( $this, 'wpml_save_delivery_time_in_default_lang_in_order_items' ), 10, 2 );
			add_filter( 'german_market_used_product_for_price_per_unit',						array( $this, 'wpml_used_product_for_price_per_unit' ) );
			add_filter( 'wp_wc_invoice_pdf_additional_pdf_tac_pages_array',						array( $this, 'wpml_additional_pdf_pages' ) );
			add_filter( 'wp_wc_invoice_pdf_additional_pdf_ecovation_pages_array',				array( $this, 'wpml_additional_pdf_pages' ) );
			add_action( 'wgm_double_opt_in_customer_registration_before_bulk_resend_email',		array( $this, 'wpml_resend_double_opt_in_before' ) );
			add_action( 'wgm_double_opt_in_customer_registration_after_bulk_resend_email',		array( $this, 'wpml_resend_double_opt_in_after' ) );
			add_filter( 'german_market_attribute_name_add_to_cart',								array( $this, 'wpml_german_market_attribute_name_add_to_cart' ), 10, 2 );

			// Register Strings
			if ( is_admin() ) {

				$tax_classes = WC_Tax::get_tax_classes();

				$tax_classes[] = 'standard';
				$tax_classes[] = '';

				foreach ( $tax_classes as $tax_class ) {

				 	$rates = WC_Tax::get_rates_for_tax_class( $tax_class );
				 	foreach ( $rates as $rate ) {
				 		$label = $rate->tax_rate_name;
				 		if ( ! icl_st_is_registered_string( 'German Market: WooCommerce Tax Rate', 'tax rate label: ' . $label ) ) {
	                        icl_register_string( 'German Market: WooCommerce Tax Rate', 'tax rate label: ' . $label, $label );
	                    }

	                    $label_with_percent = $label . sprintf( ' (%s)', str_replace( '.', wc_get_price_decimal_separator(), WC_Tax::get_rate_percent( $rate->tax_rate_id ) ) );
	                    if ( ! icl_st_is_registered_string( 'German Market: WooCommerce Tax Rate', 'tax rate label: ' . $label_with_percent ) ) {
	                    	icl_register_string( 'German Market: WooCommerce Tax Rate', 'tax rate label: ' . $label_with_percent, $label_with_percent );
	                    }
				 	}
				}

				// measuring units
				add_action( 'admin_init', function() {
					$units = get_terms( 'pa_measuring-unit', array( 'orderby' => 'slug', 'hide_empty' => 0 ) );
					if ( ! is_wp_error( $units ) ) {
						foreach ( $units as $unit ) {
							$unit_name = $unit->name;
							if ( ! icl_st_is_registered_string( 'German Market: Measuring unit name', $unit_name ) ) {
		                    	icl_register_string( 'German Market: Measuring unit name', $unit_name, $unit_name );
		                    }
							
						}
					}				
				});
			}

			foreach ( $options as $option => $default ) {

                if ( ( ! ( is_admin() && isset( $_REQUEST[ 'page' ] ) && $_REQUEST[ 'page' ] == 'german-market' ) ) || ( ! is_admin() ) ) {
                	add_filter( 'option_' . $option, array( $this, 'translate_woocommerce_checkout_options' ), 10, 2 );
                	//add_filter( 'default_option_' . $option, array ( $this, 'translate_empty_translate_woocommerce_checkout_options' ), 10, 3 );
                }

			}

		}

		/******************************
		// Compatibility with WPML: WooCommerce Multilingual
		******************************/

		// Onliy if WooCommerce Multilingual && WPML && GM Invoice PDF ADd-On
		if ( is_admin() && class_exists( 'WCML_Admin_Menus' ) && class_exists( 'SitePress' ) && ( ( get_option( 'wgm_add_on_woocommerce_invoice_pdf', 'off' ) == 'on' ) || ( get_option( 'wgm_add_on_woocommerce_return_delivery_pdf', 'off' ) == 'on' ) ) ) {

			if ( get_option( 'wgm_add_on_woocommerce_invoice_pdf', 'off' ) == 'on' ) {
				add_action( 'wp_wc_invoice_pdf_start_template', array( $this, 'wpml_invoice_pdf_admin_download_switch_lang' ) );
				add_action( 'wp_wc_invoice_pdf_end_template', array( $this, 'wpml_invoice_pdf_admin_download_reswitch_lang' ) );
				add_action( 'wp_wc_invoice_pdf_email_additional_attachment_before', array( $this, 'wpml_invoice_pdf_admin_download_switch_lang' ) );
				add_action( 'wp_wc_invoice_pdf_before_get_template_page_numbers', array( $this, 'wpml_invoice_pdf_admin_download_switch_lang' ) );
				add_action( 'wp_wc_invoice_pdf_after_get_template_page_numbers', array( $this, 'wpml_invoice_pdf_admin_download_reswitch_lang' ) );
				add_action( 'wp_wc_invoice_pdf_before_backend_download_switch', array( $this, 'wpml_invoice_pdf_admin_download_switch_lang' ) );

				// Deactivate saving pdf content
				if ( get_option( 'german_market_wpml_deactivate_saving_pdf_content', 'default' ) == 'deactivate' ) {
					add_action( 'admin_init', function() {
						add_filter( 'wp_wc_invoice_pdf_create_new_but_dont_save', '__return_true' );

						add_filter( 'woocommerce_admin_order_actions', function( $actions, $order ) {

							if ( isset( $actions[ 'invoice_pdf_delete_content' ] ) ) {
								unset( $actions[ 'invoice_pdf_delete_content' ] );
							}

							return $actions;

						}, 10,2 );
					});
				}
			}

			if ( get_option( 'wgm_add_on_woocommerce_return_delivery_pdf', 'off' ) == 'on' ) {
				add_action( 'wcreapdf_pdf_before_create', array( $this, 'wpml_retoure_pdf_admin_download_switch_lang' ), 10, 3 );
				add_action( 'wcreapdf_pdf_after_create', array( $this, 'wpml_retoure_pdf_admin_download_reswitch_lang' ), 10, 2 );
				add_action( 'wcreapdf_pdf_before_output', array( $this, 'wpml_retoure_pdf_admin_download_switch_lang' ), 10, 3 );
				add_action( 'wcreapdf_pdf_after_output', array( $this, 'wpml_retoure_pdf_admin_download_reswitch_lang' ), 10, 2 );
			}

			// Payment Method Problem in WPML
			add_filter( 'wp_wc_invoice_pdf_html_before_rendering', function( $html, $args ) {
				if ( isset( $args[ 'order' ] ) ) {
					$html = self::wpml_repair_payment_methods( $html, $args[ 'order' ], $args );
				}
				return $html;
			}, 10, 2 );

			/*
			add_filter( 'wgm_manual_order_confirmation_payment_instructions', function( $html, $order ) {
				$html = self::wpml_repair_payment_methods( $html, $order );
				return $html;
			}, 10, 2 );
			*/

		}

		/******************************
		// Compatibility with polylang
		******************************/
		if ( function_exists( 'pll_register_string' ) && function_exists( 'pll__' ) ){

			add_filter( 'german_market_backend_save_texts_only_if_not_equal_to_default_text', '__return false' );

			foreach ( $options as $option => $default  ) {
				pll_register_string( $option, get_option( $option, $default ), 'German Market: Checkout Option', true );
				add_filter( 'option_' . $option, array( $this, 'translate_woocommerce_checkout_options_polylang' ), 10, 2 );
			}

			// Delivery Times
			add_action( 'init', function() {
				$delivery_times = get_terms( 'product_delivery_times', array( 'orderby' => 'id', 'hide_empty' => 0 ) );
				foreach ( $delivery_times as $term ) {
					pll_register_string( $term->slug, $term->name, 'German Market: Delivery Time', true );
				}
			});

			add_filter( 'woocommerce_de_get_deliverytime_string_label_string', array( $this, 'polylang_woocommerce_de_get_deliverytime_string_label_string' ), 10, 2 );

		}

		/******************************
		// Compatibility with WooCommerce Composite Products
		******************************/
		if ( class_exists( 'WC_Product_Composite' ) ) {
			add_filter( 'gm_compatibility_is_variable_wgm_template', '__return_false' );
			add_filter( 'gm_cart_template_force_woocommerce_template', '__return_true' );
			add_filter( 'gm_remove_woo_vat_notice_return_original_param', '__return_true' );
		}

		/******************************
		// Compatibility WooCommerce Subscriptions
		******************************/
		if ( function_exists( 'wcs_cart_totals_order_total_html' ) ) {
			add_action( 'german_market_after_frontend_init', array( $this, 'subscriptions' ) );
		}

		if ( class_exists( 'WC_Subscriptions' ) ) {
			add_filter( 'gm_invoice_pdf_email_settings', 					array( $this, 'subscriptions_gm_invoice_pdf_email_settings' ) );
			add_filter( 'gm_invoice_pdf_email_settings_additonal_pdfs', 	array( $this, 'subscriptions_gm_invoice_pdf_email_settings' ) );
			add_filter( 'wcreapdf_allowed_stati', 							array( $this, 'subscriptions_gm_allowed_stati_additional_mals' ) );
			add_filter( 'wp_wc_inovice_pdf_allowed_stati', 					array( $this, 'subscriptions_gm_allowed_stati_additional_mals' ) );
			add_filter( 'wp_wc_inovice_pdf_allowed_stati_additional_mals', 	array( $this, 'subscriptions_gm_allowed_stati_additional_mals' ) );
			add_filter( 'wcreapdf_email_options_after_sectioned',			array( $this, 'subscriptions_gm_retoure_pdf_email_settings' ) );
			add_filter( 'gm_emails_in_add_ons', 							array( $this, 'subscriptions_gm_emails_in_add_ons' ) );
			add_filter( 'german_market_options_bcc_emails', 				array( $this, 'subscriptions_gm_bbc_cc_emails' ) );

			if ( has_filter( 'wcs_new_order_created' ) ) {
	            add_action( 'woocommerce_new_order', array( $this, 'action_woocommerce_new_order' ), 70, 1 );
	            add_filter( 'wcs_new_order_created', array( $this, 'filter_wcs_new_order_created' ), 70, 1) ;
	        }

	        add_filter( 'woocommerce_countries_inc_tax_or_vat', array( $this, 'dummy_remove_woo_vat_notice' ), 70, 1 );
	        add_filter( 'woocommerce_countries_ex_tax_or_vat', array( $this, 'dummy_remove_woo_vat_notice' ), 70, 1 );

	        // Don't copy invoice number, invoice date or due date form first order to subscription object
			add_filter( 'wcs_renewal_order_meta', array( $this, 'subscriptions_gm_dont_copy_meta' ), 10, 3 );

			// Show short descrition of product types 'subscription_variation'
			add_filter( 'german_market_get_short_description_by_product_id_check_variation', function( $boolean, $product ) {

				if ( WGM_Helper::method_exists( $product, 'get_type' ) ) {
					if ( $product->get_type() == 'subscription_variation' ) {
						$boolean = true;
					}
				}

				return $boolean;

			}, 10, 2 );

		}

		/******************************
		// Compatibility for plugins with custom email status
		******************************/
		add_filter( 'gm_invoice_pdf_email_settings', 					array( $this, 'custom_email_status_gm_invoice_pdf_email_settings' ) );
		add_filter( 'gm_invoice_pdf_email_settings_additonal_pdfs', 	array( $this, 'custom_email_status_gm_invoice_pdf_email_settings' ) );
		add_filter( 'wcreapdf_email_options_after_sectioned',			array( $this, 'custom_email_status_gm_retoure_pdf_email_settings' ) );
		add_filter( 'wcreapdf_allowed_stati', 							array( $this, 'custom_email_status_gm_allowed_stati_additional_mals' ) );
		add_filter( 'wp_wc_inovice_pdf_allowed_stati', 					array( $this, 'custom_email_status_gm_allowed_stati_additional_mals' ) );
		add_filter( 'wp_wc_inovice_pdf_allowed_stati_additional_mals', 	array( $this, 'custom_email_status_gm_allowed_stati_additional_mals' ) );
		add_filter( 'gm_emails_in_add_ons', 							array( $this, 'custom_email_status_gm_emails_in_add_ons' ) );

		/******************************
		// Theme Compabilities
		******************************/
		require_once( Woocommerce_German_Market::$plugin_path . 'inc' . DIRECTORY_SEPARATOR . 'compatibilities' . DIRECTORY_SEPARATOR . 'theme-compatibilities.php' );
		$theme_compatibilities = WGM_Theme_Compatibilities::get_instance();

		$the_theme = wp_get_theme();

		if ( $the_theme->get( 'TextDomain' ) == 'thb_text_domain' || ( $the_theme->get_template() == 'superba' ) ) {
			add_action( 'init', array( $this, 'theme_support_superba' ), 20 );
		}

		// Theme woodance
		else if ( ( ! is_admin() ) && ( $the_theme->get( 'TextDomain' ) == 'woodance' || ( $the_theme->get_template() == 'woodance' ) ) ) {
			add_action( 'wp', array( $this, 'theme_support_woodance' ) );
		}

		// Theme Envision
		else if ( $the_theme->get_template() == 'envision' || $the_theme->get_stylesheet() == 'envision' ) {
			add_action( 'german_market_after_frontend_init', array( $this, 'theme_support_envision' ) );
		}

		// Theme Fluent
		else if ( $the_theme->get_template() == 'fluent' || $the_theme->get_stylesheet() == 'fluent' || $the_theme->get_stylesheet() == 'fluent-child' ) {
			add_action( 'wp', array( $this, 'theme_support_fluent' ) );
		}

		// Theme Peddlar
		else if ( $the_theme->get_template() == 'peddlar' || $the_theme->get_stylesheet() == 'peddlar' || $the_theme->get_stylesheet() == 'peddlar-child' ) {
			add_filter( 'wgm_close_a_tag_before_wgm_product_summary_in_loop', '__return_false' );
			add_filter( 'wgm_product_summary_html', array( $this, 'theme_support_peddlar' ), 1, 4 );
		}

		// Theme VG Vegawine
		else if ( $the_theme->get_template() == 'vg-vegawine' || $the_theme->get_stylesheet() == 'vg-vegawine' || $the_theme->get_stylesheet() == 'vg-vegawine-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_vegawine' ) );
		}

		// Theme Savoy
		else if ( $the_theme->get_template() == 'savoy' || $the_theme->get_stylesheet() == 'savoy' || $the_theme->get_stylesheet() == 'savoy-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_savoy' ) );
		}

		// Theme Kryia
		else if ( $the_theme->get_template() == 'kriya' || $the_theme->get_stylesheet() == 'kriya' || $the_theme->get_stylesheet() == 'kriya-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_kriya' ) );
		}

		// Theme Hestia Pro
		else if ( $the_theme->get_template() == 'hestia-pro' || $the_theme->get_stylesheet() == 'hestia-pro' || $the_theme->get_stylesheet() == 'hestia-pro-child' || $the_theme->get_template() == 'hestia' || $the_theme->get_stylesheet() == 'hestia' || $the_theme->get_stylesheet() == 'hestia-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_hestia_pro' ), 10, 3 );
		}

		// Theme The7
		else if ( $the_theme->get_template() == 'dt-the7' || $the_theme->get_stylesheet() == 'dt-the7' || $the_theme->get_stylesheet() == 'dt-the7-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_the7' ) );

		// Theme Sober
		} else if ( $the_theme->get_template() == 'sober' || $the_theme->get_stylesheet() == 'sober-the7' || $the_theme->get_stylesheet() == 'sober-child' ) {
			add_action( 'german_market_after_frontend_init', array( $this, 'theme_support_sober' ) );
			add_action( 'wp_head', array( $this, 'theme_support_sober_css' ) );

		// Theme XStore
		} else if ( $the_theme->get_template() == 'xstore' || $the_theme->get_stylesheet() == 'xstore' || $the_theme->get_stylesheet() == 'xstore-child' ) {
			add_action( 'wp', array( $this, 'theme_support_xstore' ), 61 );
			add_filter( 'german_market_compatibility_elementor_price_data', '__return_false' );

		// Muenchen
		} else if ( $the_theme->get_template() == 'muenchen' || $the_theme->get_stylesheet() == 'muenchen' || $the_theme->get_stylesheet() == 'muenchen-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_support_cart_template_remove_taxes_in_subototal' ) );

		// Peony
		} else if ( $the_theme->get_template() == 'peony' || $the_theme->get_stylesheet() == 'peony' || $the_theme->get_stylesheet() == 'peony-child' ) {
			add_action( 'wp', array( $this, 'theme_support_peony' ), 10 );

		// Ronneby
		} else if ( $the_theme->get_template() == 'ronneby' || $the_theme->get_stylesheet() == 'ronneby' || $the_theme->get_stylesheet() == 'ronneby-child' ) {
			add_action( 'init', array( $this, 'theme_support_ronneby' ), 11 );

		// handsome-shop
		} else if ( $the_theme->get_template() == 'handmade-shop' || $the_theme->get_stylesheet() == 'handmade-shop' || $the_theme->get_stylesheet() == 'handmade-shop-child' ) {
			add_action( 'init', array( $this, 'theme_support_handmade_shop' ), 11 );

		// iustore
		} else if ( str_replace( 'iustore', '', $the_theme->get_template() ) != $the_theme->get_template() || str_replace( 'iustore', '', $the_theme->get_stylesheet() ) != $the_theme->get_stylesheet() || str_replace( 'iustore-child', '', $the_theme->get_stylesheet() ) != $the_theme->get_stylesheet() || $the_theme->get( 'TextDomain' ) == 'iustore' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_support_iustore' ) );

		// amely
		} else if ( $the_theme->get_template() == 'amely' || $the_theme->get_stylesheet() == 'amely' || $the_theme->get_stylesheet() == 'amely-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_support_amely' ), 11 );			

		// Flatsome Page Builder
		} else if ( $the_theme->get_template() == 'flatsome' || $the_theme->get_stylesheet() == 'flatsome' || $the_theme->get_stylesheet() == 'flatsome-child' ) {
			add_action( 'woocommerce_after_template_part', array( $this, 'theme_flatsome_price_data' ), 10, 4 );
			add_filter( 'german_market_compatibility_elementor_price_data', '__return_false' );

		// Ordo
		} else if ( $the_theme->get( 'TextDomain' ) == 'ordo' || $the_theme->get_template() == 'ordo' || $the_theme->get_stylesheet() == 'ordo' || $the_theme->get_stylesheet() == 'ordo-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_support_ordo' ), 10 );

		// Variegated
		} else if ( $the_theme->get_template() == 'variegated' || $the_theme->get_stylesheet() == 'variegated' || $the_theme->get_stylesheet() == 'variegated-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_support_variegated' ), 10 );
			add_action( 'wp_head', array( $this, 'theme_support_variegated_css' ) );

		// Adorn
		} else if ( $the_theme->get_template() == 'adorn' || $the_theme->get_stylesheet() == 'adorn' || $the_theme->get_stylesheet() == 'adorn-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_support_adorn' ), 10 );

		// Hypermarket
		} else if ( $the_theme->get_template() == 'hypermarket' || $the_theme->get_stylesheet() == 'hypermarket' || $the_theme->get_stylesheet() == 'hypermarket-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_support_hypermarket' ), 10 );

		// Planetshine Polaris
		} else if ( $the_theme->get_template() == 'planetshine-polaris' || $the_theme->get_stylesheet() == 'planetshine-polaris' || $the_theme->get_stylesheet() == 'planetshine-polaris-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_support_planetshine_polaris' ), 10 );

		// Electro
		} else if ( $the_theme->get_template() == 'electro' || $the_theme->get_stylesheet() == 'electro' || $the_theme->get_stylesheet() == 'electro-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_support_electro' ), 11 );
			add_filter( 'german_market_compatibility_elementor_price_data', '__return_false' );


		// Justshop
		} else if ( $the_theme->get_template() == 'justshop' || $the_theme->get_stylesheet() == 'justshop' || $the_theme->get_stylesheet() == 'justshop-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_support_jutshop' ), 11 );

		// Page Builder Framework
		} else if ( $the_theme->get( 'TextDomain' ) == 'page-builder-framework' || $the_theme->get_template() == 'page-builder-framework' || $the_theme->get_stylesheet() == 'page-builder-framework' || $the_theme->get_stylesheet() == 'page-builder-framework-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_support_page_builder_framework' ), 11 );

		// DFD Native
		}  else if ( $the_theme->get( 'TextDomain' ) == 'dfd-native' || $the_theme->get_template() == 'dfd-native' || $the_theme->get_stylesheet() == 'dfd-native' || $the_theme->get_stylesheet() == 'dfd-native-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_support_dfd_native' ), 11 );

		// Depot
		} else if ( $the_theme->get_template() == 'depot' || $the_theme->get_stylesheet() == 'depot' || $the_theme->get_stylesheet() == 'depot-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_support_depot' ), 11 );

		// Minera
		} else if ( $the_theme->get_template() == 'minera' || $the_theme->get_stylesheet() == 'minera' || $the_theme->get_stylesheet() == 'minera-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_support_minera' ), 11 );

		// Elaine
		} else if ( $the_theme->get_template() == 'elaine' || $the_theme->get_stylesheet() == 'elaine' || $the_theme->get_stylesheet() == 'elaine-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_support_elaine' ), 11 );
			add_action( 'wp_head', array( $this, 'theme_support_elaine_css' ) );

		// Yolo Robino
		} else if ( $the_theme->get_template() == 'yolo-rubino' || $the_theme->get_stylesheet() == 'yolo-rubino' || $the_theme->get_stylesheet() == 'yolo-rubino-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_support_yolo_robino' ), 11 );

		// Appetito
		} else if ( $the_theme->get_template() == 'appetito' || $the_theme->get_stylesheet() == 'appetito' || $the_theme->get_stylesheet() == 'appetito-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_support_appetito' ), 11 );

		// Superfood
		} else if ( $the_theme->get_template() == 'superfood' || $the_theme->get_stylesheet() == 'superfood' || $the_theme->get_stylesheet() == 'superfood-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_support_superfood' ), 11 );

		// TM Robin
		} else if ( $the_theme->get_template() == 'tm-robin' || $the_theme->get_stylesheet() == 'tm-robin' || $the_theme->get_stylesheet() == 'tm-robin-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_support_tm_robin' ), 11 );
			add_action( 'wp_head', array( $this, 'theme_support_tm_robin_css' ) );

		// Grosso
		} else if ( $the_theme->get_template() == 'grosso' || $the_theme->get_stylesheet() == 'grosso' || $the_theme->get_stylesheet() == 'grosso-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_support_grosso' ), 11 );
			add_action( 'wp_head', array( $this, 'theme_support_grosso_css' ) );

		// Uncode
		} else if ( $the_theme->get_template() == 'uncode' || $the_theme->get_stylesheet() == 'uncode' || $the_theme->get_stylesheet() == 'uncode-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_support_uncode' ), 11 );

		// Calafate
		} else if ( $the_theme->get_template() == 'calafate' || $the_theme->get_stylesheet() == 'calafate' || $the_theme->get_stylesheet() == 'calafate-child' ) {
			// jQuery Conflict Problem
			add_filter( 'german_market_jquery_no_conflict', function( $rtn ) { return 'no'; } );

		// DieFinhutte
		} else if ( $the_theme->get_template() == 'diefinnhutte' || $the_theme->get_stylesheet() == 'diefinnhutte' || $the_theme->get_stylesheet() == 'diefinnhutte-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_support_diefinnhutte' ), 11 );

		// Verdure
		} else if ( $the_theme->get_template() == 'verdure' || $the_theme->get_stylesheet() == 'verdure' || $the_theme->get_stylesheet() == 'verdure-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_support_verdure' ), 11 );

		// Total
		} else if ( $the_theme->get_template() == 'total' || $the_theme->get_stylesheet() == 'total' || $the_theme->get_stylesheet() == 'total-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_support_total' ), 11 );

		// Salient
		} else if ( $the_theme->get_template() == 'salient' || $the_theme->get_stylesheet() == 'salient' || $the_theme->get_stylesheet() == 'salient-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_support_salient' ), 11 );

		// Theme VG Mimosa
		} else if ( $the_theme->get_template() == 'vg-mimosa' || $the_theme->get_stylesheet() == 'vg-mimosa' || $the_theme->get_stylesheet() == 'vg-mimosa-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_mimosa' ) );

		// Theme CiyaShop
		} else if ( $the_theme->get_template() == 'ciyashop' || $the_theme->get_stylesheet() == 'ciyashop' || $the_theme->get_stylesheet() == 'ciyashopa-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_ciyashop' ) );

		// Theme Highlight
		} else if ( $the_theme->get_template() == 'highlight' || $the_theme->get_stylesheet() == 'highlight' || $the_theme->get_stylesheet() == 'highlight-child' || $the_theme->get_template() == 'highlight-pro' || $the_theme->get_stylesheet() == 'highlight-pro' || $the_theme->get_stylesheet() == 'highlight-pro-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_highlight' ) );

		// Theme Oxygen
		} else if ( $the_theme->get_template() == 'oxygen' || $the_theme->get_stylesheet() == 'oxygen' || $the_theme->get_stylesheet() == 'oxygen-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_oxygen' ) );

		// Mesmerize
		} else if ( $the_theme->get_template() == 'mesmerize' || $the_theme->get_stylesheet() == 'mesmerize' || $the_theme->get_stylesheet() == 'mesmerize-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_mesmerize' ) );

		// The Retailer
		} else if ( $the_theme->get_template() == 'theretailer' || $the_theme->get_stylesheet() == 'theretailer' || $the_theme->get_stylesheet() == 'theretailer-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_theretailer' ) );

		// SweetTooth
		} else if ( $the_theme->get_template() == 'sweettooth' || $the_theme->get_stylesheet() == 'sweettooth' || $the_theme->get_stylesheet() == 'sweettooth-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_sweettooth' ) );

		// WPLMS
		} else if ( $the_theme->get_template() == 'wplms' || $the_theme->get_stylesheet() == 'wplms' || $the_theme->get_stylesheet() == 'wplms-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_wplms' ) );

		// Hermes
		} else if ( $the_theme->get_template() == 'hermes' || $the_theme->get_stylesheet() == 'hermes' || $the_theme->get_stylesheet() == 'hermes-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_hermes' ) );

		// Shoptimizer Version: 1.7.1
		} else if ( $the_theme->get_template() == 'shoptimizer' || $the_theme->get_stylesheet() == 'shoptimizer' || $the_theme->get_stylesheet() == 'shoptimizer-child' ) {
			add_filter( 'german_market_compatibility_elementor_price_data', '__return_false' ); // Elementor

		// Kameleon
		} else if ( $the_theme->get_template() == 'kameleon' || $the_theme->get_stylesheet() == 'kameleon' || $the_theme->get_stylesheet() == 'kameleon-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_kameleon' ) );

		// Makali
		} else if ( $the_theme->get_template() == 'makali' || $the_theme->get_stylesheet() == 'makali' || $the_theme->get_stylesheet() == 'makali-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_makali' ) );

		// DIVI
		} else if (  strtolower( $the_theme->get_template() ) == 'divi' || strtolower( $the_theme->get_stylesheet() == 'divi' ) || strtolower( $the_theme->get_stylesheet() ) == 'divi-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_divi' ) );
			add_filter( 'german_market_email_footer_the_content_filter', '__return_false' );

		// NaturaLife
		} else if ( $the_theme->get_template() == 'naturalife' || $the_theme->get_stylesheet() == 'naturalife' || $the_theme->get_stylesheet() == 'naturalife-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_naturalife' ) );

		// Werkstatt
		} else if ( $the_theme->get_template() == 'werkstatt' || $the_theme->get_stylesheet() == 'werkstatt' || $the_theme->get_stylesheet() == 'werkstatt-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_werkstatt' ) );

		// Coi
		} else if ( $the_theme->get_template() == 'coi' || $the_theme->get_stylesheet() == 'coi' || $the_theme->get_stylesheet() == 'coi-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_coi' ) );

		// Vermeil
		} else if ( $the_theme->get_template() == 'vermeil' || $the_theme->get_stylesheet() == 'vermeil' || $the_theme->get_stylesheet() == 'vermeil-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_vermeil' ) );

		// Toyshop (Storefront Child)
		} else if ( $the_theme->get_stylesheet() == 'toyshop' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_toyshop' ) );

		// Extra
		} else if ( $the_theme->get_template() == 'Extra' || $the_theme->get_stylesheet() == 'Extra' || $the_theme->get_stylesheet() == 'Extra-child' ) {
			add_filter( 'german_market_email_footer_the_content_filter', '__return_false' );

		// Ken
		} else if ( $the_theme->get_template() == 'ken' || $the_theme->get_stylesheet() == 'ken' || $the_theme->get_stylesheet() == 'ken-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_ken' ) );

		// Cerato
		} else if ( $the_theme->get_template() == 'cerato' || $the_theme->get_stylesheet() == 'cerato' || $the_theme->get_stylesheet() == 'cerato-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_cerato' ) );

		// Sovereign
		} else if ( $the_theme->get_template() == 'sovereign' || $the_theme->get_stylesheet() == 'sovereign' || $the_theme->get_stylesheet() == 'sovereign-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_sovereign' ) );

		// Rehub
		} else if ( $the_theme->get_template() == 'rehub-theme' || $the_theme->get_stylesheet() == 'rehub-theme' || $the_theme->get_stylesheet() == 'rehub-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_rehub' ) );

		// Shopping
		} else if ( $the_theme->get_template() == 'shopping' || $the_theme->get_stylesheet() == 'shopping' || $the_theme->get_stylesheet() == 'shopping-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_shopping' ) );

		// Kava
		} else if ( $the_theme->get_template() == 'kava' || $the_theme->get_stylesheet() == 'kava' || $the_theme->get_stylesheet() == 'kava-child' || $the_theme->get_stylesheet() == 'hypernova' || $the_theme->get_stylesheet() == 'hypernova-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_kava' ) );

		// Bolge
		} else if ( $the_theme->get_template() == 'bolge' || $the_theme->get_stylesheet() == 'bolge' || $the_theme->get_stylesheet() == 'bolge-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_bolge' ) );

		// Eveland
		} else if ( $the_theme->get_template() == 'eveland' || $the_theme->get_stylesheet() == 'eveland' || $the_theme->get_stylesheet() == 'eveland-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_eveland' ) );

		// Faith
		} else if ( $the_theme->get_template() == 'faith-blog' || $the_theme->get_template() == 'faith' || $the_theme->get_stylesheet() == 'faith' || $the_theme->get_stylesheet() == 'faith-blog' || $the_theme->get_stylesheet() == 'faith-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_faith' ) );

		// Biagiotti
		} else if ( $the_theme->get_template() == 'biagiotti' || $the_theme->get_stylesheet() == 'biagiotti' || $the_theme->get_stylesheet() == 'biagiotti-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_biagiotti' ) );

		// Paw Friends
		} else if ( $the_theme->get_template() == 'pawfriends' || $the_theme->get_stylesheet() == 'pawfriends' || $the_theme->get_stylesheet() == 'pawfriends-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_pawfriends' ) );

		// Savory
		} else if ( $the_theme->get_template() == 'savory' || $the_theme->get_stylesheet() == 'savory' || $the_theme->get_stylesheet() == 'savory-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_savory' ) );

		// Massive Dynamic
		} else if ( $the_theme->get_template() == 'massive-dynamic' || $the_theme->get_stylesheet() == 'massive-dynamic' || $the_theme->get_stylesheet() == 'massive-dynamic-child' ) {
			add_action( 'after_setup_theme', array( $this, 'massive_dynamic' ) );

		// June
		} else if ( $the_theme->get_template() == 'june' || $the_theme->get_stylesheet() == 'june' || $the_theme->get_stylesheet() == 'june-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_june' ), 20 );

		// Mr. Tailor
		} else if ( $the_theme->get_template() == 'mrtailor' || $the_theme->get_stylesheet() == 'mrtailor' || $the_theme->get_stylesheet() == 'mrtailor-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_mrtailor' ), 20 );

		// Bridge
		} else if ( $the_theme->get_template() == 'bridge' || $the_theme->get_stylesheet() == 'bridge' || $the_theme->get_stylesheet() == 'bridge-child' ) {
			add_action( 'after_setup_theme', array( $this, 'theme_bridge' ), 20 );

		// Stockholm
		} else if ( $the_theme->get_template() == 'stockholm' || $the_theme->get_stylesheet() == 'stockholm' || $the_theme->get_stylesheet() == 'stockholm-child' ) {
			add_action( 'after_setup_theme',  array( $this, 'theme_stockholm' ), 20 );

		// My BAg
		} else if ( $the_theme->get_template() == 'mybag' || $the_theme->get_stylesheet() == 'mybag' || $the_theme->get_stylesheet() == 'mybag-child' ) {
			add_action( 'after_setup_theme',  array( $this, 'theme_mybag' ), 20 );

		// Beaver Builder Theme
		} else if ( $the_theme->get_template() == 'bb-theme' || $the_theme->get_stylesheet() == 'bb-theme' || $the_theme->get_stylesheet() == 'bb-theme-child' ) {
			add_action( 'after_setup_theme',  array( $this, 'theme_bb_theme' ), 20 );

		// Open Shop
		} else if ( $the_theme->get_template() == 'open-shop' || $the_theme->get_stylesheet() == 'open-shop' || $the_theme->get_stylesheet() == 'open-shop-child' ) {
			add_action( 'after_setup_theme',  array( $this, 'theme_open_shop' ), 20 );
		
		// Neve
		} else if ( $the_theme->get_template() == 'neve' || $the_theme->get_stylesheet() == 'neve' || $the_theme->get_stylesheet() == 'neve-child' ) {
			add_action( 'after_setup_theme',  array( $this, 'theme_neve' ), 50 );
		
		}

		/******************************
		// German Market Footer in E-Mails
		******************************/
		add_filter( 'german_market_email_footer_the_content_filter', array( $this, 'avia_advanced_layout_builder' ), 10, 2 );

		/******************************
		// Divi BodyCommerce
		******************************/
		if ( function_exists( 'bodycommerce_init' ) ) {

			if ( apply_filters( 'german_market_compatibilities_bodycommerce', true ) ) {
				add_filter( 'woocommerce_get_price_html', array( $this, 'divi_bodycommerce_get_price_html' ), 10, 2 );
			}

		}

		/******************************
		// WPBakeryVisualComposer Page Builder
		******************************/
		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'woocommerce_get_price_html', array( $this, 'wp_bakery_woocommerce_get_price_html' ), 10, 2 );
			add_filter( 'wgm_template_widget_product_item_end_echo_nothing', '__return_true' );
		}

		/******************************
		// Divi Page Builder
		******************************/
		add_action ( 'wp_enqueue_scripts', array( $this, 'divi_page_builder' ), 20 );

		/******************************
		// Plugins YITH WooCommerce Best Sellers & YITH WooCommerce Wishlist
		******************************/
		if ( defined( 'YITH_WCBSL_VERSION' ) || defined( 'YITH_WCWL' ) ) {
			add_filter( 'woocommerce_product_title', array( $this, 'plugins_yith_wl_bs' ), 10, 2 );
		}

		/******************************
		// Plugin Elementor
		******************************/
		if ( defined( 'ELEMENTOR_VERSION' ) ) {

			add_action( 'wgm_email_before_get_email_de_footer', 					array( $this, 'plugin_elementor_remove_filters' ) );
			add_action( 'wgm_email_after_get_email_de_footer', 						array( $this, 'plugin_elementor_add_filters_again' ) );

			add_action( 'wgm_sepa_direct_debit_before_apply_filters_for_content', 	array( $this, 'plugin_elementor_remove_filters' ) );
			add_action( 'wgm_sepa_direct_debit_after_apply_filters_for_content', 	array( $this, 'plugin_elementor_add_filters_again' ) );

			add_action( 'wp_wc_invoice_pdf_before_the_content',						array( $this, 'plugin_elementor_remove_filters' ) );
			add_action( 'wp_wc_invoice_pdf_after_the_content',						array( $this, 'plugin_elementor_add_filters_again' ) );

			add_action( 'gm_fic_tab_content_allergens_by_id_before_content', 		array( $this, 'plugin_elementor_remove_filters' ) );
			add_action( 'gm_fic_tab_content_allergens_by_id_after_content', 		array( $this, 'plugin_elementor_add_filters_again' ) );

			add_action( 'woocommerce_after_template_part', 							array( $this, 'plugin_elementor_price_data' ), 10, 4 );

			//add_filter( 'wgm_template_woocommerce_de_price_with_tax_hint_single_class_prefix', array( $this, 'plugin_elementor_class_prefix' ), 10, 3 );
		}

		/******************************
		// Plugin Jet Elements For Elementor
		******************************/
		if ( class_exists( 'Jet_Elements' ) ) {
			add_filter( 'german_market_price_variable_theme_extra_element', function( $element ) {
				return '.elementor-jet-single-price.jet-woo-builder p.price';
			});
		}

		/******************************
		// Plugin WooLentor - WooCommerce Elementor Addons + Builder
		******************************/
		if ( defined( 'WOOLENTOR_VERSION' ) ) {
			add_filter( 'wgm_product_summary_parts_after', 							array( $this, 'plugin_woolentor_addons' ), 10, 3 );
		}

		/******************************
		// Plugin Jet Woo Builder
		******************************/
		if ( class_exists( 'Jet_Woo_Builder' ) ) {
			add_filter( 'jet-woo-builder/template-functions/product-price', array( $this, 'plugin_jet_woo_builder_price_data' ) );
		}

		/******************************
		// Plugin Klarna Compabilities
		******************************/
		if ( function_exists( 'init_klarna_gateway' ) ) {
			add_action( 'german_market_after_frontend_init', array( $this, 'plugin_support_klarna' ) );
		}
		/******************************
		// Klarna Checkout For WooCommerce
		******************************/

		// Klarna Checkout (V3) External Payment Method for WooCommerce
		if ( class_exists( 'Klarna_Checkout_For_WooCommerce' ) ) {

			add_filter( 'german_market_checkout_after_validation_without_sec_checkout_return', function( $boolean, $data, $errors, $request_data ) {

			    if ( isset( $request_data[ '_wp_http_referer' ] ) && ( str_replace( 'kco-external-payment=paypal', '', $request_data[ '_wp_http_referer' ] ) != $request_data[ '_wp_http_referer' ] ) ) {
			        $boolean = true;
			    }

			    return $boolean;

			}, 10, 4 );

		}

		if ( class_exists( 'KCO' ) ) {
			add_filter( 'german_market_checkout_after_validation_without_sec_checkout_return', function( $boolean, $data, $errors, $request_data ) {

			    if ( isset( $request_data[ 'payment_method' ] ) && $request_data[ 'payment_method' ] == 'kco' ) {
			        $boolean = true;
			    }

			    return $boolean;

			}, 10, 4 );
		}

		/******************************
		// Plugin WPGlobus Compabilities
		******************************/
		if ( defined( 'WPGLOBUS_VERSION' ) || defined( 'WOOCOMMERCE_WPGLOBUS_VERSION' ) ) {
			update_option( 'german_market_attribute_in_product_name', 'on' );
			add_filter( 'woocommerce_de_ui_options_products', array( $this, 'wpglobus_attribute_in_product_name' ) );
		}

		/******************************
		// Plugin Woo Floating Cart
		******************************/
		if ( defined( 'WOOFC_VERSION' ) ) {
			add_action( 'german_market_after_frontend_init', array( $this, 'plugin_woo_floating_cart' ) );
		}

		/******************************
		// Plugin YITH WooCommerce Added to Cart Popup Premium
		******************************/
		if ( defined( 'YITH_WACP_VERSION' ) || defined( 'YITH_WACP_PREMIUM' ) ) {
			add_filter( 'gm_add_virtual_product_notice_not_in_ajax', '__return_true' );
		}

		/******************************
		// Plugin WooCommerce Product Bundles
		******************************/
		if ( class_exists( 'WC_Bundles' ) ) {

			add_filter( 'gm_add_mwst_rate_to_product_item_return',									array( $this, 'wc_bundles_gm_add_mwst_rate_to_product_item_return' ), 10, 3 );
			add_filter( 'gm_show_taxes_in_cart_theme_template_return_empty_string',					array( $this, 'wc_bundles_gm_tax_rate_in_cart' ), 10, 2 );
			add_filter( 'german_market_ppu_co_woocommerce_add_cart_item_data_return', 				array( $this, 'wc_bundles_dont_add_ppu_to_cart_item_data' ), 10, 4 );
			add_filter( 'german_market_ppu_co_woocommerce_add_order_item_meta_wc_3_return', 		array( $this, 'wc_bundles_dont_add_ppu_to_order_item_meta' ), 10, 4 );
			add_filter( 'german_market_delivery_time_co_woocommerce_add_cart_item_data_return', 	array( $this, 'wc_bundles_dont_add_ppu_to_cart_item_data' ), 10, 4 );
			add_filter( 'woocommerce_de_add_delivery_time_to_product_title', 						array( $this, 'wc_bundles_dont_show_delivery_time_in_order' ), 10, 3 );
			add_filter( 'wp_wc_invvoice_pdf_td_product_name_style', 								array( $this, 'wc_bundles_invoice_pdf_padding_for_bundled_products' ), 10, 2 );
			add_filter( 'wp_wc_invoice_pdf_tr_class', 												array( $this, 'wc_bundles_invoice_pdf_tr_class' ), 10, 2 );
			add_filter( 'german_market_avoid_called_twice_in_add_mwst_rate_to_product_order_item', 	'__return_true' );

			add_action( 'wp_head',																	array( $this, 'wc_bundles_styles' ) );

			add_filter( 'german_market_add_woocommerce_de_templates_force_original', function( $boolean, $template_name ) {

				if ( $template_name == 'cart/cart.php' ) {
					$boolean = true;
				}

				if ( ! has_filter( 'woocommerce_cart_item_subtotal', array( 'WGM_Template', 'add_mwst_rate_to_product_item' ) ) ) {
					add_filter( 'woocommerce_cart_item_subtotal', array( 'WGM_Template', 'show_taxes_in_cart_theme_template' ), 10, 3 );
				}

				return $boolean;

			}, 10, 2 );

			add_action( 'woocommerce_after_template_part', function( $template_name, $template_path, $located, $args ) {


				if ( $template_name == 'single-product/bundled-item-price.php' ) {

					if ( isset( $args[ 'bundled_item' ] ) ) {

						$bundled_item = $args[ 'bundled_item' ];
						if ( $bundled_item->is_priced_individually() ) {
							echo WGM_Tax::get_tax_line( $bundled_item->product );
						}

					}

				}

			}, 10, 4 );

		}

		/******************************
		// Plugin WooCommerce Multistep Checkout
		// @since 3.9.2
		// @tested with plugin version
		******************************/
		if ( function_exists( 'run_thwmsc' ) ) {

			if ( get_option( 'gm_deactivate_checkout_hooks', 'off' ) == 'off' ) {
				update_option( 'gm_deactivate_checkout_hooks', 'on' );
			}

			add_filter( 'german_market_checkout_after_validation_without_sec_checkout_return', function( $boolean, $data, $errors, $request_data ) {

				if ( isset( $request_data[ 'action' ] ) && $request_data[ 'action' ] == 'thwmsc_step_validation' ) {
					$boolean = true;
				}

				return $boolean;

			}, 10, 4 );

		}

		/******************************
		// Plugin WooCommerce Product Bundles
		******************************/
		if ( function_exists( 'woocommerce_heidelpaycw_loaded' ) ) {
			add_filter( 'wgm_double_opt_in_activation_user_roldes', array( $this, 'wgm_double_opt_in_activation_user_roldes_heideplaycw' ) );
		}

		/******************************
		// Plugin Wirecard Checkout Seamless
		******************************/
		if ( function_exists( 'init_woocommerce_wcs_gateway' ) ) {
			add_filter( 'gm_2ndcheckout_gateway_label', array( $this, 'wcs_gateway_2ndcheckout_label' ) );
		}

		/******************************
		// Plugin WooCommerce Bookings
		******************************/
		if ( class_exists( 'WC_Bookings' ) ) {
			add_filter( 'wp_wc_infoice_pdf_item_meta_end_markup', array( $this, 'wc_bookings_wp_wc_infoice_pdf_item_meta_end_markup' ), 10, 4 );
		}

		/******************************
		// Stripe Gateway
		******************************/
		if ( class_exists( 'WC_Stripe' ) ) {

			$stripe_options = get_option( 'woocommerce_stripe_settings', array() );

			if ( ( isset( $stripe_options[ 'enabled' ] ) ) && ( $stripe_options[ 'enabled' ] == 'yes' ) && ( isset( $stripe_options[ 'payment_request' ] ) ) && ( $stripe_options[ 'payment_request' ] == 'yes' ) ) {
				add_filter( 'german_market_checkout_after_validation_without_sec_checkout_return', function( $boolean, $data, $errors, $request_array ) {

					if ( isset( $request_array[ 'wc-ajax' ] ) && $request_array[ 'wc-ajax' ] == 'wc_stripe_create_order' ) {
						$boolean = true;
					}

					return $boolean;

				}, 10, 4 );
			}
		}

		/******************************
		// Plugin Product Filter for WooCommerce
		******************************/
		if ( class_exists( 'XforWC_Product_Filters' ) ) {
			add_filter( 'german_market_admin_do_not_load_gm_js', function( $do_not_load_gm_js ) {

				if ( ( isset( $_REQUEST[ 'page' ] ) ) && ( $_REQUEST[ 'page' ] == 'wc-settings' ) && ( isset( $_REQUEST[ 'tab' ] ) ) && ( $_REQUEST[ 'tab' ] == 'product_filter' ) ) {
					$do_not_load_gm_js = true;
				}

				return $do_not_load_gm_js;

			} );
		}

		/******************************
		// Plugin Extendons Product Bundles
		******************************/
		if ( class_exists( 'EXTENDONS_PRODUCT_BUNDLES' ) ){
			add_filter( 'german_market_jquery_no_conflict', function( $response ) {
				return 'no';
			} );
		}

		/******************************
 		* Plugin WooCommerce Advanced Notifications
		******************************/
		if ( class_exists( 'WC_Advanced_Notifications' ) ) {
			if ( get_option( 'woocommerce_de_manual_order_confirmation', 'off' ) == 'on' ) {
				if ( isset( $GLOBALS[ 'wc_advanced_notifications' ] ) ) {
					add_action( 'wgm_manual_order_confirmation_confirm', function( $order_id ) {
						$wc_advanced_notifications = $GLOBALS[ 'wc_advanced_notifications' ];
						$wc_advanced_notifications->new_order( $order_id );
					});
				}
			}
		}

		/******************************
 		* Plugin XL WooCommerce Sales Triggers
 		* This plugin adds the price data again in WCST_Core::wcst_wc_price_hook_checking
		******************************/
		if ( class_exists( 'WCST_Core' ) ) {
			$wcst_core = WCST_Core::get_instance();
			remove_action( 'wp', array( $wcst_core, 'wcst_wc_price_hook_checking' ), 998 );
		}

		/******************************
 		* Plugin Chained Products
		******************************/
		if ( function_exists( 'chained_product_activate' ) ) {
			add_filter( 'woocommerce_de_avoid_free_items_in_cart_by_item', function( $boolean, $item ) {

				if ( isset( $item[ 'chained_item_of' ] ) && ! empty( $item[ 'chained_item_of' ] ) ) {
					$boolean = false;
				}

				return $boolean;
			}, 10, 2 );
		}

		/******************************
 		* Plugin Amelia (Boking Plugin)
		******************************/
		if ( defined( 'AMELIA_PATH' ) ) {

			add_filter( 'wcvat_recalculate_cart_return_before', function( $boolean ) {

				if ( empty( $_REQUEST ) ) {
					$boolean = true;
				}

				if ( ! ( isset( $_REQUEST[ 'wc-ajax' ] ) && $_REQUEST[ 'wc-ajax' ] == 'update_order_review' ) ) {
					$boolean = true;
				}

				if ( ! $boolean ) {

					$debug_backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 20 );

					$wcvat_recalculate_cart = false;
					foreach ( $debug_backtrace as $elem ) {
						if ( $elem[ 'function' ] ==  'wcvat_recalculate_cart' ) {
							$wcvat_recalculate_cart = true;
							break;
						}
					}

					if ( ! $wcvat_recalculate_cart ) {
						$boolean = true;
					}

				}

				return $boolean;

			} );

		}

		/******************************
 		* Plugin WooCommerce Amazon Pay Gateway
		******************************/
		if ( class_exists( 'WC_Amazon_Payments_Advanced' ) ) {

			add_filter( 'wcvat_recalculate_cart_return_before', function( $boolean ) {

				if ( ! ( isset( $_REQUEST[ 'wc-ajax' ] ) && $_REQUEST[ 'wc-ajax' ] == 'update_order_review' ) ) {
					$boolean = true;
				}

				return $boolean;

			} );

		}

		/******************************
 		* PageBuilder Themify
		******************************/
		add_action( 'after_setup_theme', function() {
			if ( class_exists( 'Themify_Builder' ) ) {
				add_filter( 'german_market_email_compatibility_content', function( $compatibility_content, $content, $post ) {

					if ( ! empty( get_post_meta( $post->ID, '_themify_builder_settings_json', true ) ) ) {
						global $ThemifyBuilder;
	        			$page_builder_content = $ThemifyBuilder->get_builder_output( $post->ID );
					}

	        		if ( ! empty( $page_builder_content ) ) {
	        			$compatibility_content = $page_builder_content;
	        			$compatibility_content = preg_replace( '/<script\b[^>]*>(.*?)<\/script>/is', "", $page_builder_content );
	        		}

	        		return $compatibility_content;

				}, 10, 3 );
			}
		} );

		/******************************
 		* Plugin WooCommerce MultiSite Global Cart
		******************************/
		add_action( 'after_setup_theme', function() {
			if ( defined( 'WOOGC_VERSION' ) ) {

				add_action( 'german_market_before_id_to_check_is_digital', function( $item ) {
					if ( isset( $item[ 'blog_id' ] ) ) {
						switch_to_blog( $item[ 'blog_id' ] );
					}
				} );

				add_action( 'german_market_after_id_to_check_is_digital', function( $item ) {
					if ( isset( $item[ 'blog_id' ] ) ) {
						restore_current_blog();
					}
				} );
			}
		} );

		/******************************
 		* Plugin Beaver Themer
 		* since 3.10.1
 		* tested with plugin version 1.3.0.1
		******************************/
		if ( class_exists( 'FLThemeBuilderLoader' ) ) {
			add_action( 'woocommerce_after_template_part', function( $template_name, $template_path, $located, $args ) {

				if ( $template_name == 'single-product/price.php' ) {

					$exception = false;
					$debug_backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 20 );

					foreach ( $debug_backtrace as $elem ) {
						if ( $elem[ 'function' ] == 'ast_load_product_quick_view_ajax' ) {
							$exception = true;
							break;
						}
					}

					$exception = apply_filters( 'german_market_compatibilities_beaver_themer_exception', $exception );

					if ( ! $exception ) {
						echo WGM_Template::get_wgm_product_summary();
					}
				}

			}, 20, 4 );
		}

		/******************************
 		* Plugin WooCommerce MultiSite Global Cart
 		* since 3.10.1
 		* tested with plugin version 2.1.19
		******************************/
		if ( class_exists( 'WooCommerce_Waitlist_Plugin' ) ) {
			add_filter( 'german_market_my_account_registration_fields_validation_and_errors_dont_validate', function( $boolean ) {

				$debug_backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 10 );
				foreach ( $debug_backtrace as $elem ) {
					if ( $elem[ 'function' ] == 'wcwl_add_user_to_waitlist' ) {
						$boolean = true;
						break;
					}
				}

				return $boolean;

			});
		}

		/******************************
 		* Plugin WooCommerce Memberships
 		* since 3.10.3.2
 		* tested with plugin version 1.17.5
		******************************/
		if ( class_exists( 'WC_Memberships_Loader' ) ) {
			add_filter( 'german_market_my_account_registration_fields_validation_and_errors_dont_validate', function( $boolean ) {

				if ( is_admin() ) {
					$debug_backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 10 );
					foreach ( $debug_backtrace as $elem ) {
						if ( $elem[ 'function' ] == 'create_user_for_membership' ) {
							$boolean = true;
							break;
						}
					}
				}

				return $boolean;

			});
		}

		/******************************
 		* Plugin  WPC Smart Quick View for WooCommerce
 		* Add German Market Data to Price in QuickView
 		* since 3.10.2
 		* tested with plugin version 2.0.6
		******************************/
		if ( function_exists( 'woosq_init' ) ) {

			add_action( 'after_setup_theme', function() {

				$check = has_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ) );

				if ( ! $check ) {
					remove_action( 'woosq_product_summary', 'woocommerce_template_single_price', 15 );
				}

				add_action( 'woosq_product_summary', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 15 );

			}, 20 );

		}

		/******************************
 		* Plugin Ultimate Addons for Elementor
 		* since 3.10.2
 		* tested with plugin version 1.24.2
		******************************/
		add_action( 'uael_woo_products_price_after', function( $product_id, $settings ) {

			$check = has_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ) );

			if ( ! $check ) {
				add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
			}

			echo '<div>' . WGM_Template::woocommerce_de_price_with_tax_hint_loop() . '</div>';

			if ( ! $check ) {
				remove_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
			}

		}, 10, 2 );

		/******************************
 		* B2B Market
 		* since 3.10.5.0.1
 		* tested with plugin version 1.0.6.3
		******************************/
		
		/*
		* Adding Custumer VAT ID and Customer Group to billing address but not adding to shipping address
		* prevents the correct comparison of billing address and shipping address
		*/
		if ( class_exists( 'RGN_Address' ) ) {

			if ( 'show_only' === get_option( 'wp_wc_invoice_pdf_show_shipping_address', 'show' ) ) {

				add_action( 'wp_wc_invoice_pdf_start_template', function() {
					add_filter( 'bm_show_vatid_admin_order', '__return_false' );
					add_filter( 'bm_show_group_admin_order', '__return_false' );
				});

				add_action( 'wp_wc_invoice_pdf_end_template', function() {
					remove_filter( 'bm_show_vatid_admin_order', '__return_false' );
					remove_filter( 'bm_show_group_admin_order', '__return_false' );
				});
			}
		}

		/******************************
 		* Oxygen
 		* since 3.10.6.0.6
 		* tested with plugin version 3.6.1
		******************************/
		if ( function_exists( 'oxygen_can_activate_builder_compression' ) ) {

			/*
			* Invoice PDF - Additonal PDFs
			* Render Page Builder Content of Additonal PDF pages 
			*/
			add_filter( 'wp_wc_invoice_pdf_additional_pdf_content_filter', function( $content, $page ) {

				$meta = get_post_meta( $page->ID, 'ct_builder_shortcodes', true );
				if ( ! empty( $meta ) ) {
					$content = do_shortcode( $meta );
				} else {

					if ( apply_filters( 'german_market_email_footer_the_content_filter', true, $page ) ) {
						echo apply_filters( 'the_content', WGM_Template::remove_vc_shortcodes( $page->post_content ) );
					} else {
						echo WGM_Template::remove_vc_shortcodes( $page->post_content );
					}
					
				}

				return $content;

			}, 10, 2 );
		}

		do_action( 'wgm_compatibilities_after_construct', $this );
	}

	/**
	* Add "WPML Support" Menu in German Market UI
	*
	* @since v3.9.2
	* @wp-hook woocommerce_de_ui_left_menu_items
	* @param Array $menu
	* @return Array
	*/
	function add_wpml_menu( $menu ) {
		$menu[ 1 ] = array(
			'title'		=> __( 'WPML Support', 'woocommerce-german-market' ),
			'slug'		=> 'wpml-support',
			'callback'	=> array( $this, 'wpml_menu' ),
		);
		return $menu;
	}

	/**
	* Render "WPML Support" Menu in German Market UI
	*
	* @since v3.9.2
	* @return void
	*/
	function wpml_menu() {

		?>
		<h2><?php echo __( 'Admin Options', 'woocommerce-german-market' ); ?></h2>

		<p>
			<?php echo __( 'There are several admin options in German Market that you may want to translate with WPML. Follow the following steps to translate these options. ', 'woocommerce-german-market' ); ?>
		</p>

		<p>
			<ol>
				<li><?php echo( __( 'Configure WPML', 'woocommerce-german-market' ) ); ?></li>
				<li><?php echo( __( 'Activate "WPML String Translation"', 'woocommerce-german-market' ) ); ?></li>
				<li><?php echo( __( 'Set all German Market Options in your default WPML language (it is recommended that the default language is English)', 'woocommerce-german-market' ) ); ?></li>
				<li><?php echo( __( 'Translate the German Market Options with WPML String Translation.', 'woocommerce-german-market' ) ); ?></li>
			</ol>
		</p>

		<p>
			<?php echo __( 'If you have done the first three steps you will see a table below that shows you all German Market options that can be translated with WPML String Translations. This table helps you to find the corresponding strings in WPML. In the right column of each row you will find a link to WPML String Translation to get the correct string that you want to translate.', 'woocommerce-german-market' ); ?>
		</p>

		<p style="font-weight: bold;">
			<?php echo __( 'If you already have translated the German Market options using WPML String Translation and the domains "German Market: Checkout Option", "German Market: Invoice PDF", "German Market: Return Delivery Note", "German Market: Running Invoice Number", the translations are still saved in these domains and will probably work. In the case that everything is translated well, you do not have to add new translations with the table below. Otherwise, finish the translation with the table below (already known translations should automatically be recognized), afterwards delete the strings of the domains mentioned before.', 'woocommerce-german-market' ); ?>
		</p>

		<p>
			<?php echo __( 'Consider, if you change an admin option you have to update your translation for this option.', 'woocommerce-german-market' ); ?>
		</p>

		<?php

		if ( function_exists( 'icl_register_string' ) && function_exists( 'icl_t' ) && function_exists( 'icl_st_is_registered_string' ) ) {

			global $sitepress;

			$default_language = $sitepress->get_default_language();
			$current_language = $sitepress->get_current_language();

			$sitepress->switch_lang( $default_language );

			$options = WGM_Helper::get_translatable_options();

			$counter = 0;
			?>
			<table class="widefat">

				<tr>
					<th style="font-weight: bold;"><?php echo __( 'Name', 'woocommerce-german-market' ); ?></th>
					<th style="font-weight: bold;"><?php echo __( 'Value', 'woocommerce-german-market' ); ?></th>
					<th style="font-weight: bold;"><?php echo __( 'Translate in WPML String Translations', 'woocommerce-german-market' ); ?></th>
				</tr>

				<?php
				foreach ( $options as $key => $value ) {

					$current_option = get_option( $key );
					if ( empty( $current_option ) ) {
						continue;
					}

					$style = $counter%2 == 0 ? 'background-color: #ddd;' : '';
					?>
					<tr>
						<td style="<?php echo $style; ?>"><?php echo $key; ?></td>
						<td style="<?php echo $style; ?>"><?php echo get_option( $key ); ?></td>
						<td style="<?php echo $style; ?>"><a href="<?php echo get_admin_url(); ?>admin.php?page=wpml-string-translation/menu/string-translation.php&context=admin_texts_<?php echo $key; ?>" target="_blank">WPML String Translation</a></td>
					</tr>

					<?php

					$counter++;
				}
				?>
			</table>

			<br />
			<form method="post">

			<p>
				<?php
				$sitepress->switch_lang( $current_language );

				echo __( 'You can add the default German or English translations of your German Market settings by clicking one of the buttons below. First, set up all your German Market options in your default language. For instance, you have already set up all German Market settings in English. Afterwards, you can add the default German translations to the "WPML String translation" by clicking the button "Install default German translations of strings".', 'woocommerce-german-market' );
				?>

				<br /><br />
				<strong>
					<?php echo __( 'Existing string translations will be replaced!', 'woocommerce-german-market' );

					?>
				</strong>
			</p>

			<input type="submit" value="<?php echo __( 'Install default German translations of strings', 'woocommerce-german-market' ); ?>" class="button-secondary" name="german_market_install_wpml_translations_de" />
			<input type="submit" value="<?php echo __( 'Install default English translations of strings', 'woocommerce-german-market' ); ?>" class="button-secondary" name="german_market_install_wpml_translations_en" />

			<?php

			if ( isset( $_REQUEST[ 'german_market_install_wpml_translations_de' ] ) || isset( $_REQUEST[ 'german_market_install_wpml_translations_en' ] ) ) {

				if ( isset( $_REQUEST[ 'german_market_install_wpml_translations_de' ] ) ) {
					$language = 'de';
				} else {
					$language = 'en';
				}

				$sitepress->switch_lang( $language );
				$options = WGM_Helper::get_translatable_options();

				foreach ( $options as $key => $value_in_translation_language ) {

					$sitepress->switch_lang( $default_language );
					$value_in_default_language = get_option( $key );
					$sitepress->switch_lang( $language );

					if ( empty( $value_in_default_language ) ) {
						continue;
					}

					$string_id = icl_get_string_id( $value_in_default_language, 'admin_texts_' . $key );
					icl_add_string_translation( $string_id, $language, $value_in_translation_language, ICL_TM_COMPLETE );

				}

				$sitepress->switch_lang( $current_language );

				if ( isset( $_REQUEST[ 'german_market_install_wpml_translations_de' ] ) ) {
					$success_text = __( 'German string translations have been added!', 'woocommerce-german-market' );
				} else {
					$success_text = __( 'English string translations have been added!', 'woocommerce-german-market' );
				}

				?>
				<div class="notice notice-success is-dismissible">
			        <p><?php echo $success_text; ?></p>
			    </div><?php

			}

 			?>
 			</form>


 			<?php
			$sitepress->switch_lang( $current_language );

			if ( isset( $_REQUEST[ 'german_market_wpml_pdf_language_save' ] ) ) {
				update_option( 'german_market_wpml_pdf_language', $_REQUEST[ 'german_market_wpml_pdf_language' ] );
				update_option( 'german_market_wpml_deactivate_saving_pdf_content', $_REQUEST[ 'german_market_wpml_deactivate_saving_pdf_content' ] );
			}

			?>
			<form method="post">
				<br /><hr />
				<h2><?php echo __( 'PDF Languages', 'woocommerce-german-market' ); ?></h2>

				<p>
					<?php echo __( 'By default, all pdf files generated by German Market (invoice pdf, delivery note and return delivery note) are generated in the WPML order language. That is the language that the customer has used when finishing the order. If you want to change the language of the files, you have to change the WPML order language.', 'woocommerce-german-market'  ); ?>
				</p>

				<p>
					<?php echo __( 'You can force German Market to download the respective pdf files in a special language in the backend. Files that are downloaded in the frontend and files sent as email attachements will still be in the order language of the customer. You can set up a special language here:', 'woocommerce-german-market' ); ?>

					<br /><br />
					<select name="german_market_wpml_pdf_language">
						<option value="order_lang"><?php echo __( 'Order language', 'woocommerce-german-market' ); ?></option>
						<?php

						$current_option = get_option( 'german_market_wpml_pdf_language', 'order_lang' );
						$languages = apply_filters( 'wpml_active_languages', NULL, array( 'skip_missing' => 0 ) );
						foreach ( $languages as $lang ) {
							?><option value="<?php echo $lang[ 'language_code' ]; ?>" <?php selected( $lang[ 'language_code' ], $current_option ); ?>><?php echo $lang[ 'translated_name' ]; ?></option><?php
						}
						?>
					</select>

					<br /><br />

					<?php echo __( 'Important note for invoice pdfs: If an order is marked as completed, the content of the invoice pdf is saved and everytime the pdf is generated, the saved content will be uesed to output the pdf file. That means, that the language of the pdf file won\'t change without deleting the saved content before. You can delete the saved content of a pdf file by clicking the button with the "x" next to the pdf download button in the menu "WooCommerce -> Orders". Nevertheless, you have the opportunity not to save the pdf content at all. To use this opportunity, set the following option to "Deactivate saving invoice pdf content".', 'woocommerce-german-market' );
					?>

					<br /><br />

					<?php $current_option = get_option( 'german_market_wpml_deactivate_saving_pdf_content', 'default' ); ?>

					<select name="german_market_wpml_deactivate_saving_pdf_content" style="min-width: 400px;">
						<option value="default"><?php echo __( 'Default behaviour', 'woocommerce-german-market' ); ?></option>
						<option value="deactivate"  <?php selected( 'deactivate', $current_option ); ?>><?php echo __( 'Deactivate saving invoice pdf content', 'woocommerce-german-market' ); ?></option>
					</select>

					<br /><br />
					<input type="submit" value="<?php echo __( 'Save changes', 'woocommerce-german-market' ); ?>" class="button-secondary" name="german_market_wpml_pdf_language_save" />
					<br /><br />

				</p>
			</form>
			<hr />

			<?php

		}

	}

	/**
	* Plugin WP Staging
	* Restore Options for Invoice PDF Add-On & Running Invoice PDF Add-On
	*
	* @since Version: 3.10.6.0.1
	* @wp-hook after_setup_theme
	* @return void
	*/
	public static function wp_staging_repair_invoice_and_running_invoice_number_add_on_options() {

		$all_wordpress_options = wp_load_alloptions();
		$prefix = false;

		foreach ( $all_wordpress_options as $option_key => $option_value ) {
			
			if ( 
				str_replace( 'wc_invoice_', '', $option_key ) !== $option_key ||
				str_replace( 'wc_running_', '', $option_key ) !== $option_key

			) {
				
				if ( ! $prefix ) {
					$start = explode( '_', $option_key );
					if ( isset( $start[ 0 ] ) ) {
						$prefix = $start[ 0 ] . '_';
					}
				}

				if ( $prefix ) {
					$german_market_option_key = str_replace( $prefix, 'wp_', $option_key );
					
					update_option( $german_market_option_key, $option_value );
					delete_option( $option_key );
				}
			}
		}
	}

	/**
	* Theme Neve
	*
	* @since Version: 3.10.4.1
	* @wp-hook after_setup_theme
	* @tested with theme version 2.8.2
	* @return void
	*/
	public function theme_neve() {

		add_filter( 'german_market_compatibility_elementor_price_data', '__return_false' );

		if ( function_exists( 'neve_pro_run' ) ) {
			require_once( self::$theme_compatibilities_path . 'neve' . DIRECTORY_SEPARATOR . 'neve-compatibility.php' );
			$neve_compatibility = new German_Market_Neve_Compatibility();
			$neve_compatibility->init();
			return;
		}
	}

	/**
	* Theme Open Shop
	*
	* @since v3.10.3
	* @tested with theme version 1.0.9
	* @wp-hook after_setup_theme
	* @return void
	*/
	public function theme_open_shop() {
		// Loop
		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
	}

	/**
	* Theme Beaver Builder Theme
	*
	* @since v3.10.2
	* @tested with theme version 1.7.6.2
	* @wp-hook after_setup_theme
	* @return void
	*/
	public function theme_bb_theme() {
		if ( class_exists( 'FLThemeBuilderLoader' ) ) {
			add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_single' ), 10, 3 );
		}

	}

	/**
	* Theme MyBag
	*
	* @since v3.10.2
	* @tested with theme version 1.2.6
	* @wp-hook after_setup_theme
	* @return void
	*/
	public function theme_mybag() {
		// bakery
		if ( class_exists( 'Vc_Manager' ) ) {
			remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		}
	}

	/**
	* Theme Stockholm
	*
	* @since v3.10.2
	* @tested with theme version 5.2.1
	* @wp-hook after_setup_theme
	* @return void
	*/
	public function theme_stockholm() {

		// bakery
		if ( class_exists( 'Vc_Manager' ) ) {
			remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		}

	}

	/**
	* Theme Bridge
	*
	* @since v3.10.1
	* @tested with theme version 21.0
	* @wp-hook after_setup_theme
	* @return void
	*/
	public function theme_bridge() {

		// bakery
		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true' );
		}
	}

	/**
	* Theme Mr. Tailor
	*
	* @since v3.10.1
	* @tested with theme version 2.9.15
	* @wp-hook after_setup_theme
	* @return void
	*/
	public function theme_mrtailor() {

		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true' );
		}

		add_action( 'woocommerce_before_single_product_summary', function() {
			remove_action( 'woocommerce_single_product_summary_single_price', 'woocommerce_template_single_price', 10 );
		});

		add_action( 'woocommerce_before_shop_loop_item', function() {
			remove_action( 'woocommerce_after_shop_loop_item_title_loop_price', 'woocommerce_template_loop_price', 10 );
		});
		

	}

	/**
	* Theme June
	*
	* @since v3.10.1
	* @tested with theme version 1.8.1
	* @wp-hook after_setup_theme
	* @return void
	*/
	public function theme_june() {

		// bakery
		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true' );
		}

		// elementor
		add_filter( 'german_market_compatibility_elementor_price_data', '__return_false' );

		// loop
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 15 );

		// single product
		add_action( 'woocommerce_before_single_product', function() {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 6 );
		});

	}

	/**
	* Theme Massive Dynamic
	*
	* @since v3.10.1
	* @tested with theme version 7.3
	* @wp-hook after_setup_theme
	* @return void
	*/
	public function massive_dynamic() {

		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true' );
		}

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 9 );
	}

	/**
	* Theme Savory
	*
	* @since v3.10.1
	* @tested with theme version 1.9
	* @wp-hook after_setup_theme
	* @return void
	*/
	public function theme_savory() {

		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true' );
		}

		remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_price' );
	}

	/**
	* Theme Paw friends
	*
	* @since v3.10.1
	* @tested with theme version 1.0
	* @wp-hook after_setup_theme
	* @return void
	*/
	public function theme_pawfriends() {

		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
		remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		add_action( 'pawfriends_mikado_action_woo_pl_info_below_image', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 29 );

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 8 );

		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true' );
		}
	}

	/**
	* Theme Biagiotti
	*
	* @since v3.10.1
	* @tested with theme version 1.2
	* @wp-hook after_setup_theme
	* @return void
	*/
	public function theme_biagiotti() {

		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
		remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		add_action( 'biagiotti_mikado_action_woo_pl_info_below_image', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 28 );

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 8 );

		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true' );
		}
	}

	/**
	* Theme Faith
	*
	* @since v3.10.1
	* @tested with theme version 1.1
	* @wp-hook after_setup_theme
	* @return void
	*/
	public function theme_faith() {
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 11 );
	}

	/**
	* Theme Eveland
	*
	* @since v3.10.1
	* @tested with theme version 1.3.8
	* @wp-hook after_setup_theme
	* @return void
	*/
	public function theme_eveland() {

		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true' );
		}

		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 5 );

	}

	/**
	* Theme Bolge
	*
	* @since v3.10.1
	* @tested with theme version 1.2
	* @wp-hook after_setup_theme
	* @return void
	*/
	public function theme_bolge() {

		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true' );
		}

		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
		remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		add_action( 'bolge_elated_action_woo_pl_info_below_image', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 27 );

	}

	/**
	* Theme Kava
	*
	* @since v3.10.0.1
	* @tested with theme version 2.0.2
	* @wp-hook after_setup_theme
	* @return void
	*/
	public function theme_kava() {

		add_filter( 'wgm_template_get_wgm_product_summary_choose_hook', function( $hook, $woocommerce_loop ) {

			if ( $hook == 'single' ) {

				$debug_backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 10 );
				foreach ( $debug_backtrace as $elem ) {
					if ( $elem[ 'function' ] == 'plugin_jet_woo_builder_price_data' ) {
						$hook = 'loop';
						break;
					}
				}
			}

			return $hook;

		}, 10, 2 );

		add_filter( 'wgm_template_woocommerce_de_price_with_tax_hint_single_class_prefix', array( $this, 'plugin_elementor_class_prefix' ), 10, 3 );

	}

	/**
	* Theme Rehub
	*
	* @since v3.10.0.1
	* @tested with theme version 9.3.3
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_rehub() {
		add_filter( 'german_market_compatibility_elementor_price_data', '__return_false' );

		if ( class_exists( 'Vc_Manager' ) ) {
			remove_action( 'woocommerce_single_product_summary', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_single' ), 7 );
		}

		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_single' ), 10, 3 );

	}

	/**
	* Theme Sovereign
	*
	* @since v3.9.1.12
	* @tested with theme version 1.4
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_sovereign() {

		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true' );
		}
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 8 );
		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
	}

	/**
	* Theme Cerato
	*
	* @since v3.9.1.11
	* @tested with theme version 1.3.9
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_cerato() {
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 15 );
		add_filter( 'woocommerce_get_price_html', function( $price, $product ) {


			if ( WGM_Helper::method_exists( $product, 'get_type' ) ) {
				if ( $product->get_type() == 'variation' ) {
					$debug_backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 10 );
					foreach ( $debug_backtrace as $elem ) {
						if ( $elem[ 'function' ] == 'zoo_filter_woocommerce_available_variation' ) {
							ob_start();
							//add_filter( 'wgm_product_summary_parts_after', array( $this, 'theme_divi_remove_price_outpout_parts' ), 10, 3 );
							echo WGM_Template::get_wgm_product_summary( $product, 'divi_page_builder' );
							//remove_filter( 'wgm_product_summary_parts_after', array( $this, 'theme_divi_remove_price_outpout_parts' ), 10, 3 );
							$price = ob_get_clean();
							break;
						}
					}
				}
			}

			return $price;

		}, 10, 2 );

	}

	/**
	* Theme shopping
	*
	* @since v3.10.1
	* @tested with theme version 4.1.0
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_shopping() {

		// double price in loop
		remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		add_action( 'woocommerce_shop_loop_item_title',  array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), -1 );
		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );

	}

	/**
	* Theme Ken
	*
	* @since v3.9.1.9
	* @tested with theme version 4.2.1
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_ken() {

		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true' );
		}

		remove_action( 'woocommerce_single_product_summary',  'woocommerce_template_single_price', 5 );

	}

	/**
	* Theme ToyShop (Storefront Child Theme): 2x price in loop
	*
	* @since v3.9.1.9
	* @tested with theme version 2.0.19
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_toyshop() {
		remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
		add_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 2 );
		add_action( 'wp_head', array( $this, 'theme_support_css_for_theme_toyshop' ) );
	}

	/**
	* Theme ToyShop: CSS for Loop Price
	*
	* @since v3.9.1.9
	* @tested with theme version 2.0.19
	* @wp-hook wp_head
	* @return void
	*/
	function theme_support_css_for_theme_toyshop() {

		?>
		<style>
			ul.products .wgm-info { float: right; width: 100%; text-align: right; }
		</style>

		<?php

	}

	/**
	* Theme Vermeil: 2x price in product
	*
	* @since v3.9.1.1
	* @tested with theme version 1.0.0
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_vermeil() {
		add_filter( 'german_market_compatibility_elementor_price_data', '__return_false' );
	}

	/**
	* Theme Coi
	*
	* @since v3.9.2
	* @tested with theme version 1.0.5
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_coi() {

		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true' );
		}

	}

	/**
	* Theme Werkstatt
	*
	* @since v3.9.2
	* @tested with theme version 4.2.1.2
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_werkstatt() {
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 25);
	}

	/**
	* Theme Naturalife
	*
	* @since v3.9.2
	* @tested with theme version 1.7.1
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_naturalife() {
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 15 );
	}

	/**
	* Theme Divi: Price Data if intern Page Builder ist used
	*
	* @since v3.9.2
	* @tested with theme version 3.29.2
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_divi() {
		add_filter( 'wgm_template_woocommerce_de_price_with_tax_hint_single_return', array( $this, 'theme_divi_hint_single_exception' ), 10, 2 );
		add_action( 'woocommerce_get_price_html', array( $this, 'theme_divi_page_builder_price_data' ), 10, 2 );
	}

	/**
	* Theme Divi: GM Price Data after Page Builder Pri
	*
	* @since v3.9.2
	* @tested with theme version 3.29.2
	* @wp-hook wgm_template_woocommerce_de_price_with_tax_hint_single_return
	* @param String $price
	* @param WC_Product $product
	* @return String
	*/
	function theme_divi_page_builder_price_data( $price, $product ) {

		$debug_backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 10 );
		foreach ( $debug_backtrace as $elem ) {

			if ( $elem[ 'function' ] == 'et_builder_wc_render_module_template' ) {

				ob_start();

				add_filter( 'wgm_product_summary_parts_after', array( $this, 'theme_divi_remove_price_outpout_parts' ), 10, 3 );
				echo WGM_Template::get_wgm_product_summary( $product, 'divi_page_builder' );
				remove_filter( 'wgm_product_summary_parts_after', array( $this, 'theme_divi_remove_price_outpout_parts' ), 10, 3 );

				$price .= ob_get_clean();

			}
		}

		return $price;

	}

	/**
	* Theme Divi: Remove Price from output_parts
	*
	* @since v3.9.2
	* @tested with theme version 3.29.2
	* @wp-hook wgm_product_summary_parts_after
	* @param Array $output_parts
	* @param WC_Product $product
	* @param String $hook
	* @return Array
	*/
	function theme_divi_remove_price_outpout_parts( $output_parts, $product, $hook ) {

		if ( isset( $output_parts[ 'price' ] ) ) {
			unset( $output_parts[ 'price' ] );
		}

		return $output_parts;

	}

	/**
	* Theme Divi: Exception in hint single: Dont's show data if divi page builder is used
	*
	* @since v3.9.2
	* @tested with theme version 3.29.2
	* @wp-hook wgm_template_woocommerce_de_price_with_tax_hint_single_return
	* @param Boolean $boolean
	* @param WC_Product $product
	* @return Boolean
	*/
	function theme_divi_hint_single_exception( $boolean, $product ) {

		// WPML Compatibilty
		if ( function_exists( 'icl_object_id' ) ) {

			global $sitepress;

			if ( WGM_Helper::method_exists( $sitepress, 'get_default_language' ) ) {

				$default_wpml_language = $sitepress->get_default_language();

				if ( WGM_Helper::method_exists( $product, 'get_id' ) ) {
					$default_language_product_id = icl_object_id( $product->get_id(), get_post_type( $product->get_id() ), false, $default_wpml_language );
					$product_default_language = wc_get_product( $default_language_product_id );
					if ( WGM_Helper::method_exists( $product_default_language, 'get_meta' ) ) {
						if ( $product_default_language->get_meta( '_et_pb_use_builder' ) == 'on' ) {
							$version = $product_default_language->get_meta( '_et_builder_version' );
							if ( str_replace( 'VB|Divi|', '', $version ) != $version ) {
								$version = str_replace( 'VB|Divi|', '', $version );
								if ( version_compare( $version, '4.0', '>' ) ) {
									$boolean = true;
								}
							} else if ( str_replace( 'BB|Divi|', '', $version ) != $version ) {
								$version = str_replace( 'BB|Divi|', '', $version );
								if ( version_compare( $version, '3.16', '>=' ) ) {
									$boolean = true;
								}
							}
						}  else if ( $product->get_meta( '_et_pb_use_builder' ) == 'off' ) {
							$boolean = true;
						}
					}
				}
			}
		}

		if ( WGM_Helper::method_exists( $product, 'get_meta' ) ) {
			if ( $product->get_meta( '_et_pb_use_builder' ) == 'on' ) {
				$version = $product->get_meta( '_et_builder_version' );
				if ( str_replace( 'VB|Divi|', '', $version ) != $version ) {
					$version = str_replace( 'VB|Divi|', '', $version );
					if ( version_compare( $version, '4.0', '>' ) ) {
						$boolean = true;
					}
				} else if ( str_replace( 'BB|Divi|', '', $version ) != $version ) {
					$version = str_replace( 'BB|Divi|', '', $version );
					if ( version_compare( $version, '3.16', '>=' ) ) {
						$boolean = true;
					}
				}
			} else if ( $product->get_meta( '_et_pb_use_builder' ) == 'off' ) {
				$boolean = true;
			}
		}

		return $boolean;
	}

	/**
	* Theme Makali: 2x price
	*
	* @since v3.9.2
	* @tested with theme version 1.2.8
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_makali() {

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 15 );
		remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );

		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true' );
		}

	}

	/**
	* Theme Kameleon: 2x price
	*
	* @since v3.9.2
	* @tested with theme version 2.0
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_kameleon() {

		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , function( $boolean, $elem_function, $debug_backtrace ) {

				$boolean = true;

				foreach ( $debug_backtrace as $debug ) {
					if ( $debug[ 'function' ] == 'kameleon_woocommerce_maker' ) {
						$boolean = false;
					}
				}

				return $boolean;
			}, 10, 3 );
		}

	}

	/**
	* Theme Hermes: 2x price, Bakery
	*
	* @since v3.9.2
	* @tested with theme version 1.7.1
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_hermes() {

		// Bakery
		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , function( $boolean, $elem_function, $debug_backtrace ) {
				if ( $elem_function == 'woocommerce_template_single_price' ) {
					return true;
				}
				return $boolean;
			}, 10, 3 );
		}

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 25 );

	}

	/**
	* Theme WPLMS: 2x price
	*
	* @since v3.9.2
	* @tested with theme version 3.9.5
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_wplms() {

		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true' );
		}

	}

	/**
	* Theme SweetTooth: 2x price in product
	*
	* @since v3.9.2
	* @tested with theme version 1.3
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_sweettooth() {

		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true' );
		}

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 8 );
		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
		remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		add_action( 'sweettooth_elated_action_woo_pl_info_below_image', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 27 );
	}

	/**
	* Theme Retailer: 2x price in shop, change position
	*
	* @since v3.9.1
	* @tested with theme version 2.15
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_theretailer() {

		add_action( 'woocommerce_single_product_summary_single_price', function() {
			remove_action( 'woocommerce_single_product_summary_single_price', 'woocommerce_template_single_price', 10 );
		}, 1 );

		remove_action( 'woocommerce_single_product_summary', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_single' ), 7 );

		add_action( 'woocommerce_single_product_summary_single_price', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_single' ), 10 );

	}

	/**
	* Theme Mesmerize: 2x price in loop
	*
	* @since v3.9.1
	* @tested with theme version 1.6.82
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_mesmerize() {
		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
		remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		add_action( 'woocommerce_after_template_part', array( $this, 'theme_support_mesmerize_loop_price' ), 10, 4 );
	}

	/**
	* Theme Mesmerize: Price in Loop
	*
	* @since v3.9.1
	* @tested with theme version 1.6.82
	* @wp-hook woocommerce_after_template_part
	* @param String $template_name
	* @param String $template_path
	* @param String $located
	* @param Array $args
	* @return void
	*/
	function theme_support_mesmerize_loop_price( $template_name, $template_path, $located, $args ){

		if ( $template_name == 'loop/price.php' ) {
			WGM_Template::woocommerce_de_price_with_tax_hint_loop();
		}

	}

	/**
	* Theme Oxygen: Price and Bakery Builder
	*
	* @since v3.8.2
	* @tested with theme version 5.2.5
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_oxygen() {


		// Single
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 20 );

		// Loop
		if ( class_exists( 'Vc_Manager' ) ) {
			remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		} else {
			add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
		}

	}

	/**
	* Theme Highlight: 2x Price in Loop
	*
	* @since v3.8.2
	* @tested with theme version 1.0.15
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_highlight() {
		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
	}

	/**
	* Theme CiyaShop: 2x Price in Loop
	*
	* @since v3.8.2
	* @tested with theme version 3.4.2
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_ciyashop() {

		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true' );
		}

		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
	}

	/**
	* Theme Total: 2x Price in Loop
	*
	* @since v3.8.2
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_salient() {

		remove_action( 'nectar_woo_minimal_price', 'woocommerce_template_loop_price', 5 );
		add_filter( 'wgm_product_summary_html', function( $output_html, $output_parts, $product, $hook ) {

			if ( $hook == 'loop' ) {

				$output_html = str_replace( 'class="price"', 'class="german-market-salient-price"', $output_html );

			}

			if ( class_exists( 'Vc_Manager' ) ) {
				add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true' );
			}

			return $output_html;

		}, 10, 4 );
	}

	/**
	* Theme Total: 2x Price Data in Widgets caused bei Bakery
	*
	* @since v3.8.2
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_total() {

		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true' );
		}

		// Widget Correction
		remove_action( 'woocommerce_after_template_part', array( 'WGM_Template', 'widget_after_content_product' ), 10, 4 );
		add_action( 'woocommerce_get_price_html', array( $this, 'theme_support_total_widget_price' ), 10, 3 );

	}

	/**
	* Theme Total: 2x Price Data in Widgets caused bei Bakery
	*
	* @since v3.8.2
	* @wp-hook woocommerce_get_price_html
	* @param Stirng $price
	* @param WC_Product $product
	* @return String
	*/
	function theme_support_total_widget_price ( $price, $product ) {

		$debug_backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 10 );
		foreach ( $debug_backtrace as $elem ) {

			if ( $elem[ 'function' ] == 'dynamic_sidebar' ) {
				ob_start();
				echo WGM_Template::get_wgm_product_summary();
				$price = ob_get_clean();
				break;
			}
		}

		return $price;

	}

	/**
	* German Market E-Maill footer does not contain page content
	*
	* @since v3.8.2
	* Theme Enfold with Avia Advanced Layout Builder, tested with Theme Version 4.4.1
	* @wp-hook german_market_email_footer_the_content_filter
	* @return void
	*/
	function avia_advanced_layout_builder( $boolean, $post ) {

		if ( function_exists( 'Avia_Builder' ) ) {
			if ( '' != Avia_Builder()->get_alb_builder_status( $post->ID ) ) {
				$boolean = false;
			}
		}

		return $boolean;

	}

	/**
	* Theme Verdure: 2x
	*
	* @since v3.8.2
	* @tested with theme version 1.1
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_verdure() {

		// page builder exception
		if ( class_exists( 'Vc_Manager' ) ) {
			remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
			remove_action( 'woocommerce_single_product_summary', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_single' ), 7 );
			add_action( 'wp_head', array( $this, 'theme_support_css_for_bakery_fixes_in_loop' ) );
		}

	}

	/**
	* Style Fixes For Themes With Bakery Exception
	*
	* @since v3.8.2
	* @wp-hook wp_head
	* @return void
	*/
	function theme_support_css_for_bakery_fixes_in_loop() {
		?>
		<style>
		 	ul.products > .product .price .woocommerce-de_price_taxrate {
				font-size: 0.8em;
			}

			ul.products > .product .price .price-per-unit {
				display: block;
				font-size: x-small;
			}

			ul.products > .product .price .woocommerce_de_versandkosten {
				font-size: 0.8em;
				display: block;
			}

			ul.products > .product .price .wgm-kleinunternehmerregelung {
				font-size: 0.8em;
				display: block;
			}

			ul.products > .product .price .wgm-info, .gm-wp_bakery_woocommerce_get_price_html .wgm-info {
				line-height: 18px;
			}

		</style>
		<?php
	}

	/**
	* Theme DieFinnhutte: 2xPrice in Loop
	*
	* @since v3.8.2
	* @tested with theme version 1.1
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_diefinnhutte() {
		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
		remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
	}

	/**
	* Remove price if $hook == 'single' but we are in a theme or 3rd plugin loop
	*
	* @since v3.9.1
	* @wp-hook wgm_product_summary_parts_after
	* @param Array $output_parts
	* @param WC_Product $product
	* @param String $hook
	* @return Array
	*/
	function theme_support_remove_price_in_other_loops( $output_parts, $product, $hook ) {

		if ( $hook == 'single' && isset( $output_parts[ 'price' ] ) ) {

			$is_caroussel = false;

			$debug_backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 10 );

			foreach ( $debug_backtrace as $debug ) {
				if ( $debug[ 'function' ] == 'woocommerce_de_price_with_tax_hint_loop' ) {
					$is_caroussel = true;
					break;
				}
			}

			if ( $is_caroussel ) {
				unset( $output_parts[ 'price' ] );
			}
		}

		return $output_parts;
	}

	/**
	* Theme Uncode: [digital][digital]
	*
	* @since v3.8.2
	* @tested with theme version 2.0.2
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_uncode() {

		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true' );
		}

		add_filter( 'woocommerce_cart_item_name', array( $this, 'theme_support_uncode_remove_double_digital' ), 99, 3 );

		// Loop
		remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		add_filter( 'wgm_product_summary_parts_after', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );

		// Avoid double price after price is printed by uncode_create_single_block in loops
		add_filter( 'wgm_product_summary_parts_after', function( $output_parts, $product, $hook ) {

			if ( $hook == 'single' && isset( $output_parts[ 'price' ] ) ) {

				$is_caroussel = false;
				$debug_backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 20 );

				foreach ( $debug_backtrace as $debug ) {
					if ( $debug[ 'function' ] == 'uncode_create_single_block' ) {
						$is_caroussel = true;
						break;
					}
				}

				if ( $is_caroussel ) {
					unset( $output_parts[ 'price' ] );
				}
			}

			return $output_parts;
		}, 10, 3 );

		// Add German Market data after price is printed by uncode_create_single_block in loops
		add_filter( 'woocommerce_get_price_html', function( $price, $product ) {

			$debug_backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 10 );

			$is_get_price_html = false;
			$is_uncode_create_single_block = false;

			foreach ( $debug_backtrace as $elem ) {

				if ( $elem[ 'function' ] == 'get_price_html' ) {
					$is_get_price_html = true;
				} else if ( $elem[ 'function' ] == 'uncode_create_single_block' ) {
					$is_uncode_create_single_block = true;
				}

				if ( $is_get_price_html && $is_uncode_create_single_block ) {
					break;
				}

			}

			if ( $is_get_price_html && $is_uncode_create_single_block ) {
				ob_start();
				echo WGM_Template::get_wgm_product_summary( $product, 'uncode_single_block' );
				$extra_data = apply_filters( 'german_market_wp_bakery_data', ob_get_clean() );
				$price .= $extra_data;
			}

			return $price;

		}, 10, 2 );

	}

	/**
	* Theme Uncode: [digital][digital] Callback
	*
	* @since v3.8.2
	* @tested with theme version 2.0.2
	* @wp-hook woocommerce_cart_item_name
	* @return String
	*/
	function theme_support_uncode_remove_double_digital ( $title, $cart_item, $cart_item_key ) {
		return str_replace( '[Digital] [Digital]', '[Digital]', $title );
	}

	/**
	* Theme Grosso: Double Price in loop
	*
	* @since v3.8.2
	* @tested with theme version 1.3.1
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_grosso() {
		add_filter( 'german_market_wp_bakery_price_html_exception', '__return_true' );
		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
	}

	/**
	* Theme Grosso:  Price CSS Hack
	*
	* @since v3.8.2
	* @tested with theme version 1.3.1
	* @wp-hook wp
	* @return void
	*/
	function theme_support_grosso_css() {
		?>
		<style>
			.woocommerce.single-product .product .summary .legacy-itemprop-offers .price {
				width: 100%;
			}
		</style>
		<?php
	}

	/**
	* Theme TM Robin: Double Price
	*
	* @since v3.8.2
	* @tested with theme version 1.7.7
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_tm_robin() {

		//loop
		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
		remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		add_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 11 );
		add_filter( 'german_market_wp_bakery_price_html_exception', '__return_true' );

		// single
		remove_action( 'woocommerce_single_product_summary', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_single' ), 7 );
		add_action( 'woocommerce_single_product_summary', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_single' ), 11 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	}

	/**
	* Theme TM Robin: Double Price CSS Hack
	*
	* @since v3.8.2
	* @tested with theme version 1.7.7
	* @wp-hook wp
	* @return void
	*/
	function theme_support_tm_robin_css() {
		?>
		<style>
			.woocommerce.single-product .product .summary .wgm-info {
				font-size: 12px;
			}
		</style>
		<?php
	}

	/**
	* Theme Kanna: Double Price
	*
	* @since v3.8.2
	* @tested with theme version 1.1
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_appetito() {

		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
		remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		add_action( 'appetito_mikado_action_woo_pl_info_below_image', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 28 );

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 8 );

	}

	/**
	* Theme Superfood: Double Price
	*
	* @since v3.8.2
	* @tested with theme version 1.4
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_superfood() {

		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
		remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		add_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 28 );

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 8 );

	}

	/**
	* Plugin JetWooBuilder For Elementor (Add GM Data after)
	*
	* @since v3.8.2
	* @tested with plugin version 1.3.6
	* @wp-hook jet-woo-builder/template-functions/product-price
	* @param  String $price
	* @return String
	*/
	function plugin_jet_woo_builder_price_data( $price ) {

		ob_start();
		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
		WGM_Template::woocommerce_de_price_with_tax_hint_single();
		remove_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );

		$gm_data = ob_get_clean();

		return $price . $gm_data;
	}

	/**
	* Theme Yolo Robino: Double Price single product
	*
	* @since v3.8.2
	* @tested with theme version 1.3.4
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_yolo_robino( ) {
		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_single' ), 10, 3 );
		remove_action( 'woocommerce_single_product_summary', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_single' ), 7 );
		add_action( 'woocommerce_single_product_summary', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_single' ), 28 );
	}

	/**
	* Theme Elaine: Double Price in loop and single product
	*
	* @since v3.8.2
	* @tested with theme version 1.0
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_elaine() {

		// loop
		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
		remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		add_action( 'elaine_edge_action_woo_pl_info_below_image', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 28 );

		// single
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 15 );
	}

	/**
	* Theme Elaine: Align German Market Data to center
	*
	* @since v3.8.2
	* @tested with theme version 1.0
	* @wp-hook wp_head
	* @return void
	*/
	function theme_support_elaine_css() {
		?>
		<style>
			.edgtf-pl-text-wrapper .wgm-info {
				text-align: center;
			}
		</style>
		<?php
	}

	/**
	* Theme minera: GM Data in loop is missing
	*
	* @since v3.8.2
	* @tested with theme version 2.6
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_minera() {
		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
		add_action( 'minera_after_shop_loop_price ', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 10 ); // there's a small bug in the theme
		add_action( 'minera_after_shop_loop_price', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 10 );  // if they will fix it, it'll still work
	}


	/**
	* Theme depot: Double Price
	*
	* @since v3.8.2
	* @tested with theme version 1.4
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_depot() {
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 8 );
		remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
	}

	/**
	* WPBakeryVisualComposer: Price Infos after Price
	*
	* @since v3.8.1
	* @wp-hook woocommerce_get_price_html
	* @param String $price
	* @param WC_Product $product
	* @return String
	*/
	function wp_bakery_woocommerce_get_price_html( $price, $product ) {

		$debug_backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 10 );
		$has_loop_action = false;

		foreach ( $debug_backtrace as $elem ) {

			if ( $elem[ 'function' ] == 'woocommerce_grouped_add_to_cart' ) {
				return $price;
			} else if ( $elem[ 'function' ] == 'woocommerce_de_price_with_tax_hint_loop' || $elem[ 'function' ] == 'woocommerce_de_price_with_tax_hint_single' || $elem[ 'function' ] == 'get_available_variation' ) {
				$has_loop_action = true;
				break;
			} else if ( apply_filters( 'german_market_wp_bakery_price_html_exception', false, $elem[ 'function' ], $debug_backtrace ) ) {
				$has_loop_action = true;
				break;
			}

		}

		foreach ( $debug_backtrace as $elem ) {

			if ( $elem[ 'function' ] == 'vc_do_shortcode'  ) {

				ob_start();
				add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
				echo WGM_Template::get_wgm_product_summary( $product, 'vc_do_shortcode' );
				remove_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
				$price .= ob_get_clean();
				break;

			} else if ( $elem[ 'function' ] == 'wp_bakery_woocommerce_get_price_html' ) {

					if ( $has_loop_action ) {
						return $price;
					}

					ob_start();
					remove_filter( 'wgm_product_summary_parts', array( 'WGM_Template', 'add_product_summary_price_part' ), 0 );
					echo '<div class="gm-wp_bakery_woocommerce_get_price_html">';
					echo WGM_Template::get_wgm_product_summary( $product, 'wp_bakery_woocommerce_get_price_html' );
					echo '</div>';
					add_filter( 'wgm_product_summary_parts', array( 'WGM_Template', 'add_product_summary_price_part' ), 0, 3 );
					$price .= ob_get_clean();
					break;

			}

		}

		return $price;
	}

	/**
	* Divi BodyCommerce: Price Infos after Price
	*
	* @since v3.8.2
	* @wp-hook woocommerce_get_price_html
	* @param String $price
	* @param WC_Product $product
	* @return String
	*/
	function divi_bodycommerce_get_price_html( $price, $product ) {

		$debug_backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 10 );
		$has_loop_action = false;
		foreach ( $debug_backtrace as $elem ) {

			if ( $elem[ 'function' ] == 'woocommerce_de_price_with_tax_hint_loop' || $elem[ 'function' ] == 'woocommerce_de_price_with_tax_hint_single' || $elem[ 'function' ] == 'get_available_variation' ) {
				$has_loop_action = true;
				break;
			} else if ( apply_filters( 'german_market_divi_bodycommerce_get_price_html_exception', false, $elem[ 'function' ], $debug_backtrace ) ) {
				$has_loop_action = true;
				break;
			}

		}

		if ( $has_loop_action ) {
			return $price;
		}

		foreach ( $debug_backtrace as $elem ) {

			if ( $elem[ 'function' ] == 'divi_bodycommerce_get_price_html' ) {

				ob_start();
				remove_filter( 'wgm_product_summary_parts', array( 'WGM_Template', 'add_product_summary_price_part' ), 0 );
				echo '<div class="gm-wp_bakery_woocommerce_get_price_html">';
				echo WGM_Template::get_wgm_product_summary( $product, 'divi_bodycommerce_get_price_html' );
				echo '</div>';
				add_filter( 'wgm_product_summary_parts', array( 'WGM_Template', 'add_product_summary_price_part' ), 0, 3 );
				$price .= ob_get_clean();
				break;

			}
		}

		return $price;
	}

	/**
	* Theme DFD Native: Double Price
	*
	* @since v3.8.1
	* @tested with theme Version 1.4.0
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_dfd_native() {

		remove_action('woocommerce_after_shop_loop_item_title', 'dfd_woocommerce_template_loop_price', 5 );

		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_single' ), 10, 3 );
		remove_action( 'woocommerce_single_product_summary', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_single' ), 7 );
		add_action( 'woocommerce_single_product_summary', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_single' ), 21 );

	}

	/**
	* Theme Page Builder Framework: Double Price in Loop
	*
	* @since v3.8.1
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_page_builder_framework() {

		// shop
		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
		remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		add_action( 'wpbf_woo_loop_after_price',array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ) );
		add_filter( 'gm_add_price_in_loop_for_grouped_products_again', '__return_false' );

	}

	/**
	* Plugin WooCommerce Bookings: Show Data in Invoice PDF
	*
	* @since v3.8.1
	* @tested with plugin version 1.12.2
	* @wp-hook wp_wc_infoice_pdf_item_meta_end_markup
	* @return void
	*/
	function wc_bookings_wp_wc_infoice_pdf_item_meta_end_markup( $item_meta_end, $item_id, $item, $order ) {
		return str_replace( ' &rarr;', '', $item_meta_end );
	}

	/**
	* Theme Justshop: Double Price on Product Page
	*
	* @since v3.8.1
	* @tested with theme version 4.6
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_jutshop() {
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 20 );
	}

	/**
	* Theme Planetshine Polaris: Double Price on Product Page and Quickview
	*
	* @since v3.8.1
	* @tested with theme version 1.1.36
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_planetshine_polaris() {
		add_action( 'woocommerce_before_single_product_summary', array( $this, 'theme_support_planetshine_polaris_quickview' ) );
	}

	/**
	* Theme Planetshine Polaris: Double Price on Product Page and Quickview
	*
	* @since v3.8.1
	* @tested with theme version 1.1.36
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_planetshine_polaris_quickview() {
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 7 );
	}

	/**
	* Theme Hypermarket: Replace GM Info in Loop
	*
	* @since v3.8.1
	* @tested with theme version 1.5.5
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_hypermarket() {
		remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
		add_action('woocommerce_after_shop_loop_item', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 31 );
	}

	/**
	* Theme Electro
	*
	* @since v3.8.1 
	* @updated 3.10.4.1
	* @tested with theme version 2.5.8
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_electro() {

		// loop
		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
		remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		add_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 131 );

		// single
		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_single' ), 10, 3 );
		remove_action( 'woocommerce_single_product_summary', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_single' ), 7 );
		add_action( 'electro_single_product_action', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_single' ), 21 );

		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true' );
		}

	}

	/**
	* Theme Adorn: Doubled Price on product page and loop
	*
	* @since v3.7.2
	* @tested with theme version 1.1
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_adorn() {

		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true' );
		}

		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
		remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		add_action( 'adorn_edge_woo_pl_info_below_image', array( $this, 'theme_support_adorn_loop_price' ), 27 );

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 8 );

	}

	/**
	* Theme Adorn: Correct Loop Data
	*
	* @since v3.7.2
	* @tested with theme version 1.1
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_adorn_loop_price() {
		echo '<span class="price" style="display: block;">';
			WGM_Template::woocommerce_de_price_with_tax_hint_loop();
		echo '</span>';
	}

	/**
	* Theme Variegated: Doubled Price on product page and loop
	*
	* @since v3.7.2
	* @tested with theme version 1.0.0
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_variegated() {

		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
		remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		add_action( 'woocommerce_after_shop_loop_item', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 3 );

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 11 );

	}

	/**
	* Theme Variegated: Doubled Price on product page and loop, reorder on single product
	*
	* @since v3.7.2
	* @tested with theme version 1.0.0
	* @wp-hook wp_head
	* @return void
	*/
	function theme_support_variegated_css() {
		?>
		<style>
			.woocommerce.single-product div.product .product_title, .woocommerce .single-product div.product .product_title {
				order: 0;
			}
		</style>
		<?php
	}

	/**
	* Divi Page Builder: JS Conflicts
	*
	* @since v3.7.2
	* @wp-hook wp_enqueue_scripts
	* @return void
	*/
	function divi_page_builder() {

		if ( has_action( 'wp_footer', 'et_fb_wp_footer' ) ) {
			wp_dequeue_script( 'woocommerce_de_frontend' );
		}

	}

	/**
	* Theme Ordo: Doubled Price on product page
	*
	* @since v3.7.2
	* @tested with theme version 1.1.4
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_ordo() {
		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
		remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		add_action( 'ftc_after_shop_loop_item', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 90 );
		remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 6);
	}

	/**
	* Plugins YITH WooCommerce Best Sellers & YITH WooCommerce Wishlist: Doubled [Digital]
	*
	* @since v3.7.2
	* @tested with plugin YITH WooCommerce Best Sellers Premium version 1.1.4
	* @tested with plugin YITH WooCommerce Wishlist Premium version 2.2.4
	* @wp-hook woocommerce_product_title
	* @param String $title
	* @param WC_Product $product
	* @return String
	*/
	function plugins_yith_wl_bs( $title, $product ) {
		return str_replace( '[Digital] [Digital]', '[Digital]', $title );
	}

	/**
	* Theme Flatsome: Add German Market Data after single price
	*
	* @since 	v3.7.2
	* @tested with theme version 3.6.1
	* @wp-hook 	woocommerce_after_template_part
	* @param 	String $template_name
	* @param 	String $template_path
	* @param 	String $located
	* @param 	Array $args
	* @return 	void
	*/
	function theme_flatsome_price_data( $template_name, $template_path, $located, $args ) {

		if ( $template_name == 'single-product/price.php' || $template_name == '/single-product/price.php' ) {

			$debug_backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 1 );

			if ( isset( $debug_backtrace[ 0 ][ 'function' ] ) && ( $debug_backtrace[ 0 ][ 'function' ] === 'theme_flatsome_price_data' ) ) {

				add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
				add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_single' ), 10, 3 );
				WGM_Template::woocommerce_de_price_with_tax_hint_single();
				remove_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_single' ), 10, 3 );
				remove_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );

			}

		}

	}

	/**
	* Plugin Elementor: Add extra class prefix to legacy-itemprop-offers
	* used for variable products: don't hide price infos of products in widgets
	*
	* @since 	v3.10.01
	* @wp-hook 	wgm_template_woocommerce_de_price_with_tax_hint_single_class_prefix
	* @param 	String $class_prefix
	* @param 	String $call_function
	* @param 	WC_Product $product
	* @return 	String
	*/
	public function plugin_elementor_class_prefix( $class_prefix, $call_function, $product ) {

		$debug_backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 20 );

		$is_single_product = false;

		foreach ( $debug_backtrace as $debug ) {
			if ( $debug[ 'function' ] == 'woocommerce_template_single_price' ) {
				$is_single_product = true;
				break;
			}
		}

		if ( ! $is_single_product ) {
			$class_prefix = '-not-single';
		}

		return $class_prefix;

	}

	/**
	* Plugin Elementor: Add German Market Data after single price
	*
	* @since 	v3.7.2
	* @tested with plugin version 2.2.5
	* @wp-hook 	woocommerce_after_template_part
	* @param 	String $template_name
	* @param 	String $template_path
	* @param 	String $located
	* @param 	Array $args
	* @return 	void
	*/
	function plugin_elementor_price_data( $template_name, $template_path, $located, $args ) {

		if ( $template_name == 'single-product/price.php' || $template_name == '/single-product/price.php' ) {

			$debug_backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 10 );

			// Exception for some Themes
			$exceptions = array(
				'ASTRA_Ext_WooCommerce_Markup'
			);

			foreach ( $debug_backtrace as $debug ) {

				if ( isset( $debug[ 'class' ] ) ) {
					if ( in_array( $debug[ 'class' ], $exceptions ) ) {
						return;
					}
				}
			}

			if ( isset( $debug_backtrace[ 0 ][ 'function' ] ) && ( $debug_backtrace[ 0 ][ 'function' ] === 'plugin_elementor_price_data' ) ) {

				if ( apply_filters( 'german_market_compatibility_elementor_price_data', true ) ) {

					add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_single' ), 10, 3 );
					WGM_Template::woocommerce_de_price_with_tax_hint_single( 'plugin_elementor_price_data' );
					remove_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_single' ), 10, 3 );

				}

			}

		}

	}

	/**
	* Plugin Woolentor Addons: Double Price in QuickView
	*
	* @since v3.9.2
	* @tested with plugin version 1.5.3
	* @wp-hook wgm_product_summary_parts_after
	* @param Array $output_parts
	* @param WC_Product $product
	* @param String $hook
	* @return Array
	*/
	function plugin_woolentor_addons( $output_parts, $product, $hook ) {

		$debug_backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 20 );
		foreach ( $debug_backtrace as $elem ) {
			if ( $elem[ 'function' ] == 'woolentor_wc_quickview' ) {
				if ( isset( $output_parts[ 'price' ] ) ) {
					unset( $output_parts[ 'price' ] );
				}
			}
		}

		return $output_parts;

	}

	/**
	* Plugin Elementor: Remove Filters from Plugin
	* before sending Email with German Market Content
	*
	* @since v3.7.1
	* @tested with plugin version 2.1.6
	* @wp-hook wgm_email_before_get_email_de_footer
	* @return void
	*/
	function plugin_elementor_remove_filters() {

		$elementor = Elementor\Plugin::instance();
		$elementor->frontend->remove_content_filter();

	}

	/**
	* Plugin Elementor: Add filters again from plugin
	* after sending Email with German Market Content
	*
	* @since v3.7.1
	* @tested with plugin version 2.1.6
	* @wp-hook wgm_email_after_get_email_de_footer
	* @return void
	*/
	function plugin_elementor_add_filters_again() {

		$elementor = Elementor\Plugin::instance();
		$elementor->frontend->add_content_filter();

	}

	/**
	* Theme Amely: Doubled Price in loop and singe product pages
	*
	* @since v3.7
	* @tested with theme version 1.6.1
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_amely() {
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 5 );
		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_single' ), 10, 3 );
	}

	/**
	* Plugin Wirecard Checkout Seamless: Show chosen payment type label on 2nd Checkout Page
	*
	* @since v3.7
	* @tested with plugin version 1.0.18
	* @wp-hook gm_2ndcheckout_gateway_label
	* @paran String $label
	* @return String
	*/
	function wcs_gateway_2ndcheckout_label( $label ) {

		$payment_type = WGM_Session::get( 'wcs_payment_method', 'first_checkout_post_array' );
		
		$paymentClass = 'WC_Gateway_Wirecard_Checkout_Seamless_'. str_replace( '-', '_', ucfirst( strtolower( $payment_type ) ) );
		
		if ( ! class_exists( $paymentClass ) ) {
			$paymentClass = 'WC_Gateway_Qenta_Checkout_Seamless_'. str_replace( '-', '_', ucfirst( strtolower( $payment_type ) ) );
		}

		if ( class_exists( $paymentClass ) ) {
			$paymentClass = new $paymentClass( array() );
			$label = $paymentClass->get_label();
		}

		return $label;
	}

	/**
	* Theme iustore: Doubled Price in loop and singe product pages
	*
	* @since v3.6.4
	* @tested with theme version 1.8
	* @wp-hook init
	* @return void
	*/
	function theme_support_iustore() {
		remove_action( 'woocommerce_single_product_summary','woocommerce_template_single_price',15 );
		add_filter( 's7upf_product_price', array( $this, 's7upf_product_price' ) );
	}

	/**
	* Theme iustore: Remove Theme Price
	*
	* @since v3.6.4
	* @tested with theme version 1.8
	* @wp-hook s7upf_product_price
	* @return void
	*/
	function s7upf_product_price( $html ) {
		return '';
	}

	/**
	* Theme Elessi: handsome-shop
	*
	* @since v3.6.3
	* @tested with theme version 1.0.9
	* @wp-hook init
	* @return void
	*/
	function theme_support_handmade_shop() {
		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
		add_action( 'wgm_product_summary_parts', array( $this, 'theme_support_wgm_remove_price_in_summary_parts_in_shop' ), 10, 3 );
	}

	/**
	* Plugin HeidelpayCw
	*
	* With this Plugin registered customer have the user role "Subscriber" instead of "Customer"
	*
	* @since v3.6.3
	* @tested with plugin version 3.0.182
	* @wp-hook wgm_double_opt_in_activation_user_roldes
	* @return void
	*/
	function wgm_double_opt_in_activation_user_roldes_heideplaycw( $user_roles ) {
		$user_roles[] = 'Subscriber';
		return $user_roles;
	}

	/**
	* Theme Ronneby: Price in Loop & Product Pages
	*
	* @since v3.6.3
	* @tested with theme version 2.4.7
	* @wp-hook init
	* @return void
	*/
	function theme_support_ronneby() {
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 11 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 15 );
	}

	/**
	* Plugin WooCommerce Product Bundles: No GM Data for Variations
	*
	* @since 3.8.2
	* @wp-hook wp_head
	* @return void
	*/
	function wc_bundles_styles(){

		?>
		<style>
			.bundled_item_cart_details .wgm-info{ display: none; }
			.bundled_item_cart_details .price + .wgm-info.woocommerce-de_price_taxrate{ display: block; }
			table.shop_table td.product-subtotal span.wgm-tax { display: none; }
			table.shop_table td.product-subtotal .woocommerce-Price-amount ~ span.wgm-tax { display: block; }
		</style>
		<?php
	}

	/**
	* Plugin WooCommerce Product Bundles: Add tr class to bundled items
	*
	* @since 3.10.4
	* @wp-hook wp_wc_invoice_pdf_tr_class
	* @param String $tr_class
	* @param Item $args
	* @return String
	*/
	public function wc_bundles_invoice_pdf_tr_class( $tr_class, $item ) {
		if ( isset( $item[ 'bundled_by' ] ) ) {
			$tr_class .= ' bundled';
		}
		return trim( $tr_class );
	}

	/**
	* Plugin WooCommerce Product Bundles: Order Item Names in Invoice PDFs
	*
	* @since 3.10.2
	* @wp-hook wp_wc_invvoice_pdf_td_product_name_style
	* @param String $style
	* @param Item $args
	* @return String
	*/
	public function wc_bundles_invoice_pdf_padding_for_bundled_products( $style, $item ) {

		if ( isset( $item[ 'bundled_by' ] ) ) {
			$style = 'padding-left: 2em;';
		}

		return $style;

	}

	/**
	* Plugin WooCommerce Product Bundles: Don't show taxes of bundled items
	*
	* @since 3.8.2
	* @wp-hook gm_add_mwst_rate_to_product_item_return
	* @param Boolean $booleand
	* @param WC_Prodcut $product
	* @param Array $item
	* @return Bollean
	*/
	public function wc_bundles_gm_add_mwst_rate_to_product_item_return( $boolean, $product, $cart_item ) {

		if ( isset( $cart_item[ 'bundled_by' ] ) ) {
			$boolean = true;

			if ( isset( $cart_item[ 'data' ]->bundled_cart_item->item_data ) ) {

				$item_data = $cart_item[ 'data' ]->bundled_cart_item->item_data;

				if ( isset( $item_data[ 'priced_individually' ] ) && $item_data[ 'priced_individually' ] == 'yes' ) {
					$boolean = false;
				}

			}
		}

		return $boolean;
	}

	/**
	* Plugin WooCommerce Product Bundles: Don't add PPU to order item data
	*
	* @since v3.8.2
	* @wp-hook german_market_ppu_co_woocommerce_add_order_item_meta_wc_3_return
	* @param Boolean $boolean
	* @param Integer $item_id
	* @param Array $item
	* @param Integer $order_id
	* @return Boolean
	*/
	function wc_bundles_dont_add_ppu_to_order_item_meta( $boolean, $item_id, $item, $order_id ) {

		if ( isset( $item[ 'bundled_by' ] ) ) {
			$boolean = true;
		}

		return $boolean;

	}

	/**
	* Plugin WooCommerce Product Bundles: Don't show delivery time in orders
	*
	* @since v3.8.2
	* @wp-hook woocommerce_de_add_delivery_time_to_product_title
	* @param String $return
	* @param String $item_name
	* @param Array $item
	* @return String
	*/
	function wc_bundles_dont_show_delivery_time_in_order( $return, $item_name, $item ) {

		if ( isset( $item[ 'bundled_by' ] ) ) {
			$return = $item_name;
		}

		return $return;

	}

	/**
	* Plugin WooCommerce Product Bundles: Don't add PPU to cart item data
	*
	* @since v3.8.2
	* @wp-hook german_market_ppu_co_woocommerce_add_cart_item_data_return
	* @wp-hook german_market_delivery_time_co_woocommerce_add_cart_item_data_return
	* @param Boolean $boolean
	* @param Array $cart_item_data
	* @param Integer $product_id
	* @param Integer $variation_id
	* @return String
	*/
	function wc_bundles_dont_add_ppu_to_cart_item_data( $boolean, $cart_item_data, $product_id, $variation_id ) {

		if ( isset( $cart_item_data[ 'bundled_by' ] ) ) {
			$boolean = true;
		}

		return $boolean;
	}

	/**
	* Plugin WooCommerce Product Bundles: Don't show taxes of bundled items
	*
	* @since v3.8.2
	* @wp-hook gm_show_taxes_in_cart_theme_template_return_empty_string
	* @param Boolean $string
	* @param Array $cart_item
	* @return Boolean
	*/
	public function wc_bundles_gm_tax_rate_in_cart( $boolean, $cart_item ) {

		if ( isset( $cart_item[ 'bundled_by' ] ) ) {

			$boolean = true;

			if ( isset( $cart_item[ 'data' ]->bundled_cart_item->item_data ) ) {

				$item_data = $cart_item[ 'data' ]->bundled_cart_item->item_data;

				if ( isset( $item_data[ 'priced_individually' ] ) && $item_data[ 'priced_individually' ] == 'yes' ) {
					$boolean = false;
				}

			}

		}

		return $boolean;
	}

	/**
	* Theme Peony: Price in Loop
	*
	* @since v3.5.9
	* @wp-hook wp
	* @return void
	*/
	function theme_support_peony() {
		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
	}

	/**
	* Themes that uses an old version of 'cart/cart.php' of German Market
	* AND / OR
	* uses out cart table with tasxes but uses 'woocommerce_cart_item_subtotal' hook to display subtotals
	*
	* @since v3.5.8
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_cart_template_remove_taxes_in_subototal() {
		remove_action( 'woocommerce_checkout_init', array( 'WGM_Template', 'add_mwst_rate_to_product_item_init' ) );

	}

	/**
	* Theme XStore: Price in Product Pages
	*
	* @tested with theme version 7.2.3
	* @last-updated: v3.10.6.0.1
	* @since v3.5.7
	* @wp-hook wp
	* @return void
	*/
	function theme_support_xstore() {

		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true' );
		}

		if ( is_product() ) {

			$product = wc_get_product();

			if ( $product->get_type() == 'variable' ) {

				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 25 );

			} else {

				add_action( 'wgm_product_summary_parts', array( $this, 'theme_support_wgm_remove_price_in_summary_parts_in_shop' ), 10, 3 );
				remove_action( 'woocommerce_single_product_summary', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_single' ), 7 );
				add_action( 'woocommerce_single_product_summary', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_single' ), 26 );

			}

		}

	}

	/**
	* Theme Sober: Checkboxes
	*
	* @since v3.8.2
	* @wp-hook wp_head
	* @return void
	*/
	function theme_support_sober_css() {
		?>
		<style>
			.woocommerce-checkout form.checkout .form-row.german-market-checkbox-p{ padding-left: 0; }
		</style>
		<?php
	}
	/**
	* Theme Sober: Price in Shop
	*
	* @since v3.5.6
	* @wp-hook german_market_after_frontend_init
	* @return void
	*/
	function theme_support_sober(){

		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true' );
		}

		add_action( 'wgm_product_summary_parts', array( $this, 'theme_support_wgm_remove_price_in_summary_parts_in_shop' ), 10, 3 );
		remove_action( 'woocommerce_single_product_summary', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_single' ), 7 );
		add_action( 'woocommerce_single_product_summary', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_single' ), 21 );

		add_filter( 'german_market_price_variable_theme_extra_element', function( $element ) {
			return '.summary.entry-summary p.price';
		});
	}

	/**
	* Theme Support: Remove GM Price in Shop
	*
	* @since v3.5.3
	* @wp-hook wgm_product_summary_parts
	* @param Array $output_parts
	* @param WC_Product $product
	* @param String $hook
	* @return Array
	*/
	function theme_support_wgm_remove_price_in_summary_parts_in_shop( $output_parts, $product, $hook ) {

		if ( $hook == 'single' ) {

			if ( isset( $output_parts[ 'price' ] ) ) {
				unset( $output_parts[ 'price' ] );
			}

		}

		return $output_parts;
	}

	/**
	* WPML: Get translated product to get copied price per unit
	*
	* @since v3.9.2
	* @wp-hook wpml_used_product_for_price_per_unit
	* @param WC_Product $_product
	* @return WC_Product
	*/
	function wpml_used_product_for_price_per_unit( $_product ) {

		if ( WGM_Helper::method_exists( $_product, 'get_id' ) ) {
			$default_lang = apply_filters( 'wpml_default_language', NULL );
			$id = $_product->get_id();
			$id = apply_filters( 'wpml_object_id', $id, 'product', true, $default_lang );
			$_product = wc_get_product( $id );
		}

		return $_product;
	}

	/**
	* WPML: Translate Measuring Unit
	*
	* @since v3.11
	* @wp-hook german_market_measuring_unit
	* @param String $unit
	* @param Boolean|String $translation_lang
	* @return String
	*/
	public function wpml_translate_measuring_unit( $unit, $translation_lang = false ) {

		global $sitepress;
		$lang = $sitepress->get_current_language();
		$default_lang = $sitepress->get_default_language();

		if ( $lang == $default_lang ) {
			return $unit;
		}

		if ( $translation_lang ) {
			$lang = $translation_lang;
		}

		$unit = apply_filters( 'wpml_translate_single_string', $unit, 'German Market: Measuring unit name', $unit, $lang );

		return $unit;
	}

	/**
	* WPML: Translate Measuring Unit
	*
	* @since v3.11
	* @wp-hook german_market_ppu_co_woocommerce_order_formatted_line_subtotal
	* @param String $unit_string
	* @param WC_Order_Item $item
	* @param WC_Order $order
	* @param Boolean|String $translation_lang
	* @return String
	*/
	public function wpml_translate_measuring_unit_in_order( $unit_string, $item, $order, $translation_lang = false ) {

		global $sitepress;
		$lang = $sitepress->get_current_language();
		$default_lang = $sitepress->get_default_language();

		if ( $translation_lang ) {
			$lang = $translation_lang;
		}

		$order_language = $order->get_meta( 'wpml_language' );
		if ( empty( $order_language ) ) {
			$order_language = $lang;
		}

		if ( $order_language === $lang ) {
			return $unit_string;
		}

		$units_order_language 	= array();
		$units_current_language = array();

		$sitepress->switch_lang( $default_lang );
		$units = get_terms( 'pa_measuring-unit', array( 'orderby' => 'slug', 'hide_empty' => 0 ) );
		$sitepress->switch_lang( $lang );

		if ( is_wp_error( $units ) ) {
			return $unit_string;
		}

		foreach ( $units as $unit ) {
			if ( strlen( $unit->name ) >= 3 ) {
				$units_order_language[]   = apply_filters( 'wpml_translate_single_string', $unit->name, 'German Market: Measuring unit name', $unit->name, $order_language );
				$units_current_language[] = apply_filters( 'wpml_translate_single_string', $unit->name, 'German Market: Measuring unit name', $unit->name, $lang );
			}
		}

		$count = 1;
		$unit_string = str_replace( $units_order_language, $units_current_language, $unit_string, $count );

		return $unit_string;
	}

	/**
	* WPML: Translate delivery times
	*
	* @since v3.5.5
	* @wp-hook woocommerce_de_get_deliverytime_label_term
	* @param Object $label_term
	* @param WC_Product $product
	* @return void
	*/
	function wpml_translate_delivery_times( $label_term, $product ) {

		if ( apply_filters( 'gm_wpml_has_wrong_settings_for_label_terms', false ) ) {
			return $label_term;
		}

		global $sitepress;
		$default_wpml_language = $sitepress->get_default_language();

		if ( ! WGM_Helper::method_exists( $product, 'get_id' ) ) {
			return $label_term;
		}

		$default_language_product_id = icl_object_id( $product->get_id(), get_post_type( $product->get_id() ), false, $default_wpml_language );
		if ( $default_language_product_id > 0 ) {
			$term_id = WGM_Template::get_term_id_from_product_meta( '_lieferzeit', wc_get_product( $default_language_product_id ) );
			$new_term_id = icl_object_id( $term_id, 'product_delivery_times', true, $sitepress->get_current_language() );
			$label_term = get_term( $new_term_id, 'product_delivery_times' );

		}

		return $label_term;
	}

	/**
	* WPML: Save delivery time in order item meta, id of term in default wpml language
	*
	* @since v3.8.2
	* @wp-hook add_deliverytime_to_order_item
	* @param Integer $term_id
	* @param WC_Order_Product $product
	* @return Array
	*/
	function wpml_save_delivery_time_in_default_lang_in_order_items( $term_id, $product ) {

		global $sitepress;
		$default_wpml_language = $sitepress->get_default_language();

		if ( WGM_Helper::method_exists( $product, 'get_id' ) ) {

			$default_language_product_id = icl_object_id( $product->get_id(), get_post_type( $product->get_id() ), false, $default_wpml_language );
			if ( $default_language_product_id > 0 ) {
				$new_term_id = icl_object_id( $term_id, 'product_delivery_times', true, $default_language_product_id );
				$label_term = get_term( $new_term_id, 'product_delivery_times' );
			}
		}

		return $term_id;
	}

	/**
	* WPML: Translate Pages of Addional PDFs in Invoice PDF Add-On
	*
	* @since v3.8.2
	* @wp-hook wp_wc_invoice_pdf_additional_pdf_ecovation_pages_array
	* @wp-hook wp_wc_invoice_pdf_additional_pdf_tac_pages_array
	* @param Array $pages
	* @return Array
	*/
	function wpml_additional_pdf_pages( $pages ) {

		if ( function_exists( 'icl_object_id' ) ) {
			foreach ( $pages as $key => $page ) {
				$pages[ $key ] = icl_object_id( $page->ID );
			}
		}

		return $pages;
	}

	/**
	* Theme The7: Price in Loop
	*
	* @since v3.5.5
	* @last-updated v3.10.1
	* @tested with theme version 8.6.0
	* @wp-hook after_setup_theme
	* @return void
	*/
	public function theme_the7() {

		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true' );
		}

		remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		add_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 10 );
		add_action( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );

	}

	/**
	* Theme Hestia Pro: Price in Loop
	*
	* @since v3.5.5
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_hestia_pro() {

		// double price in loop
		remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
		add_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 20 );

		// double price in grouped products
		add_filter( 'gm_add_price_in_loop_for_grouped_products_again', '__return_false' );
	}

	/**
	* Remove Price in Shop
	*
	* @since v3.5.4
	* wp-hook wgm_product_summary_parts
	* @param Array $parts
	* @param WC_Product $product
	* @param String $hook
	* @return String
	**/
	function theme_support_hide_gm_price_in_loop( $parts, $product, $hook ) {

		if ( $hook == 'loop' && isset( $parts[ 'price' ] ) ) {
			unset( $parts[ 'price' ] );
		}

		return $parts;

	}

	/**
	* Remove Price in Single Pages
	*
	* @since v3.7
	* wp-hook wgm_product_summary_parts
	* @param Array $parts
	* @param WC_Product $product
	* @param String $hook
	* @return String
	**/
	function theme_support_hide_gm_price_in_single( $parts, $product, $hook ) {

		if ( $hook == 'single' && isset( $parts[ 'price' ] ) ) {
			unset( $parts[ 'price' ] );
		}

		return $parts;
	}

	/**
	* Theme Kryia: Price in Loop
	*
	* @since v3.5.3
	* @wp-hook german_market_after_frontend_init
	* @return void
	*/
	function theme_kriya() {
		remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_kriya_wgm_product_summary_parts' ), 10, 3 );
		add_action( 'woocommerce_after_shop_loop_item', array( $this, 'theme_kriya_woocommerce_de_price_with_tax_hint_loop' ), 10 );
	}

	/**
	* Theme Kryia: Price in Loop, add DIVs
	*
	* @since v3.5.3
	* @wp-hook woocommerce_after_shop_loop_item
	* @return void
	*/
	function theme_kriya_woocommerce_de_price_with_tax_hint_loop() {

		global $product;

		if ( is_a( $product, 'WC_Product_Grouped' ) ) {
			return;
		}

		echo "<div class='product-details german-market-loop-infos-for-kriya-theme'>";
			echo WGM_Template::get_wgm_product_summary();
		echo "</div>";

	}

	/**
	* Theme Kryia: Price in Loop, don't show GM Price
	*
	* @since v3.5.3
	* @wp-hook wgm_product_summary_parts
	* @param Array $output_parts
	* @param WC_Product $product
	* @param String $hook
	* @return Array
	*/
	function theme_kriya_wgm_product_summary_parts( $output_parts, $product, $hook ) {

		if ( $hook == 'loop' ) {

			if ( isset( $output_parts[ 'price' ] ) ) {
				unset( $output_parts[ 'price' ] );
			}

		}

		return $output_parts;
	}

	/**
	* Theme Savoy: Payment Gateways in Checkout and TOC just once
	*
	* @version v3.6.2
	* @since v3.5.3
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_savoy() {

		if ( get_option( 'gm_deactivate_checkout_hooks', 'off' ) == 'off' ) {

			if ( apply_filters( 'gm_theme_support_savoy_deactivate_gm_checkout_hooks', true ) ) {
				update_option( 'gm_deactivate_checkout_hooks', 'on' );
			}

		}

		// remove_action( 'woocommerce_de_add_review_order', array( 'WGM_Template', 'terms_and_conditions' ) ); // changed in 3.6.2

	}

	/**
	* Theme VG Vegawine: Remove double price in shop
	*
	* @since v3.5.3
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_vegawine() {
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 15 );
	}

	/**
	* Theme VG Mimosa: Remove double price in shop
	*
	* @since v3.8.2
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_mimosa() {

		if ( class_exists( 'Vc_Manager' ) ) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true' );
		}

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 15 );
		add_filter( 'wgm_product_summary_parts', array( $this, 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
		add_action( 'woocommerce_after_shop_loop_item', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 4 );
	}

	/**
	* Theme Woo Floating Cart: Qunatity in mini cart does not show up
	*
	* @since v3.5.3
	* @wp-hook german_market_after_frontend_init
	* @return void
	*/
	function plugin_woo_floating_cart() {
		remove_action( 'woocommerce_widget_cart_item_quantity',	array( 'WGM_Template', 'mini_cart_price' ), 10, 3 );
	}

	/**
	* Theme Peddlar: Don't show any <a>-tags in product summary in loop
	*
	* @since v3.5.3
	* @wp-hook wgm_product_summary_html
	* @return void
	*/
	function theme_support_peddlar( $output_html, $output_parts, $product, $hook ) {
		return strip_tags( $output_html, '<p></span><div><del><ins><strong><small>' );
	}

	/**
	* Plugin WPGlobus: Always set the option "Product Attributes in product name" to on
	*
	* @since v3.5.3
	* @wp-hook woocommerce_de_ui_options_products
	* @param Array $options
	* @return Array
	*/
	function wpglobus_attribute_in_product_name( $options ) {

		$options[ 'attribute_in_product_name' ] = array(
			'name'     => __( 'Product Attributes in product name', 'woocommerce-german-market' ),
			'desc_tip' => __( 'As default, the variation attributes are shown in the product name since WooCommerce 3.0. If this option is deactivated, the attributes are shown separated under the product name.', 'woocommerce-german-market' ),
			'id'       => 'german_market_attribute_in_product_name',
			'type'     => 'wgm_ui_checkbox',
			'default'  => 'on',
			'desc'	   => __( 'Because of using the Plugin "WPGlobus" and / or "WooCommerce WPGlobus", this option cannot be turned off.', 'woocommerce-german-market' ),
			'custom_attributes' => array( 'disabled' => 'disabled' ),
		);

		return $options;

	}

	/**
	* Theme Envision: "Cart Estimate Notice" is shown twice, because the theme brings exactly the same notice
	*
	* @since v3.5.1
	* @wp-hook german_market_after_frontend_init
	* @return void
	*/
	function theme_support_envision() {
		remove_filter( 'woocommerce_proceed_to_checkout', array( 'WGM_Template', 'add_cart_estimate_notice' ), 0 );
	}

	/**
	* Plugin Polylang Support: Show delivery times in the correct way
	*
	* @since v3.5.1
	* @last update v3.8.2
	* @wp-hook woocommerce_de_get_deliverytime_string_label_string
	* @param String $string
	* @param WC_Product $product
	* @return String
	*/
	function polylang_woocommerce_de_get_deliverytime_string_label_string( $string, $product ) {
		return pll__( $string );
	}

	/**
	* Plugin Klarna Support: Change behaviour how to send confirmation email
	*
	* @since v3.5.1
	* @wp-hook german_market_after_frontend_init
	* @return void
	*/
	function plugin_support_klarna() {

		add_filter( 'gm_email_confirm_order_send_it', array( $this, 'klarna_email_confirm_order_send_it' ), 10, 2 );
		add_action( 'woocommerce_thankyou', array( $this, 'klarna_woocommerce_thankyou_send_email_confirm_order' ) );
	}

	/**
	* Plugin Klarna Support: Do not send order confirmation email at processed order
	*
	* @since v3.5.1
	* @wp-hook gm_email_confirm_order_send_it
	* @return void
	*/
	function klarna_email_confirm_order_send_it( $boolean, $order ) {

		$payment_method = $order->get_payment_method();
		if ( str_replace( 'klarna', '', $payment_method ) != $payment_method ) {
			$boolean = false;
		}

		return $boolean;
	}

	/**
	* Plugin Klarna Support: Send order confirmation email on thankyou page
	*
	* @since v3.5.1
	* @wp-hook woocommerce_thankyou
	* @return void
	*/
	function klarna_woocommerce_thankyou_send_email_confirm_order( $order_id ) {

		$order = wc_get_order( $order_id );
		$payment_method = $order->get_payment_method();

		if ( str_replace( 'klarna', '', $payment_method ) != $payment_method ) {

			if ( empty( $order->get_meta( '_gm_email_confirm_order_send' ) ) ) {

				WGM_Email::send_order_confirmation_mail( $order_id );
				$order->update_meta_data( '_gm_email_confirm_order_send', 'yes' );
				$order->save_meta_data();

			}

		}

	}

	/**
	* Theme Support woodance: Display prices correctly (not twice) on single products and variable product pages
	*
	* @wp-hook wp
	* @return void
	*/
	function theme_support_woodance() {

		if ( is_product() ) {

			$product = wc_get_product();

			if ( $product->get_type() == 'simple' ) {

				remove_action( 'woocommerce_single_product_summary', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_single' ), 7 );
				add_action( 'woocommerce_before_add_to_cart_button', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_single' ), 21 );
				remove_filter( 'wgm_product_summary_parts', array( 'WGM_Template', 'add_product_summary_price_part' ), 0, 3 );

			} else if ( $product->get_type() == 'variable' ) {

				remove_action( 'woocommerce_single_product_summary', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_single' ), 7 );
				add_action( 'woocommerce_before_variations_form', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_single' ), 21 );
				add_action( 'woocommerce_before_add_to_cart_form', array( $this, 'theme_support_woodance_no_price' ) );
				add_action( 'woocommerce_before_variations_form', array( $this, 'theme_support_woodance_no_price_add_again' ) );

			}

		}

	}

	/**
	* Theme Support fluent: Display prices correctly (not twice) on single products and variable product pages
	*
	* @wp-hook wp
	* @return void
	*/
	function theme_support_fluent() {

		if ( is_product() ) {

			$product = wc_get_product();

			if ( $product->get_type() == 'simple' ) {

				remove_action( 'woocommerce_single_product_summary', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_single' ), 7 );
				add_action( 'woocommerce_single_product_summary', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_single' ), 8 );
				remove_filter( 'wgm_product_summary_parts', array( 'WGM_Template', 'add_product_summary_price_part' ), 0, 3 );

			} else if ( $product->get_type() == 'variable' ) {

				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 7 );

			}

		}

	}

	/**
	* Theme Support woodance: Display prices correctly (not twice) on single products and variable product pages
	* Remove Price html for variable products
	*
	* @wp-hook woocommerce_before_add_to_cart_form
	* @return void
	*/
	function theme_support_woodance_no_price() {
		add_filter( 'woocommerce_get_price_html', array( $this, 'theme_support_woodance_variable_price' ) );
	}

	/**
	* Theme Support woodance: Display prices correctly (not twice) on single products and variable product pages
	* Add Price html filter for variable products again
	*
	* @wp-hook woocommerce_before_variations_form
	* @return void
	*/
	function theme_support_woodance_no_price_add_again() {
		remove_filter( 'woocommerce_get_price_html', array( $this, 'theme_support_woodance_variable_price' ) );
	}

	/**
	* Theme Support woodance: Removes price html for variable product
	*
	* @wp-hook woocommerce_get_price_html
	* @return void
	*/
	function theme_support_woodance_variable_price( $price, $product ) {
		return '';
	}

	/**
	* Translateable Due Date Options
	*
	* @access public
	* @wp-hook init
	* @return void
	*/
	function due_date() {

		if ( ! is_admin() ) {
			return;
		}

		if ( ( isset( $_REQUEST[ 'page' ] ) && ( $_REQUEST[ 'page' ] == 'wc-settings' || $_REQUEST[ 'page' ] == 'german-market' ) ) ) {
			return;
		}

		$gateways = WC()->payment_gateways()->payment_gateways();
		$strings  = array();

		foreach ( $gateways as $payment_method_id => $gateway ) {

			if ( isset( $gateway->settings[ 'wgm_due_date_text' ] ) ) {
				$due_date_text 	= $gateway->settings[ 'wgm_due_date_text' ];
			} else {
				$due_date_text 	= apply_filters( 'woocommerce_de_due_date_text_' . $payment_method_id, __( 'Due Date: {{due-date}}', 'woocommerce-german-market' ) );
			}

			if ( function_exists( 'icl_register_string' ) && function_exists( 'icl_t' ) && function_exists( 'icl_st_is_registered_string' ) ) {

				if ( ! ( icl_st_is_registered_string( 'German Market: Due Date Option', $due_date_text ) ) ) {

					icl_register_string( 'German Market: Due Date Option', $due_date_text, $due_date_text );

				}

			} else if( function_exists( 'pll_register_string' ) && function_exists( 'pll__' ) ) {

					pll_register_string( $due_date_text, $due_date_text, 'German Market: Due Date Option', true );

			}

		}

	}

	/**
	* Theme Superba Support: Double price in loop and single product pages
	*
	* @access public
	* @wp-hook after_setup_theme
	* @return void
	*/
	function theme_support_superba() {

		// avoid double price in loop
		remove_action( 'woocommerce_after_shop_loop_item', 			'thb_loop_product_end', 999 );
		remove_action( 'woocommerce_after_shop_loop_item_title', 	array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		add_action( 'woocommerce_after_shop_loop_item', 			array( $this, 'superba_woocommerce_after_shop_loop_item' ), 999 );

		// avoid douple price in single product
		remove_action( 'woocommerce_single_product_summary',		array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_single' ), 7 );
		remove_action('woocommerce_single_product_summary', 'thb_single_product_summary');
		add_action( 'woocommerce_single_product_summary', array( $this, 'superba_woocommerce_single_product_summary' ), 999 );
	}

	/**
	* Theme Superba Support: Double price in loop
	*
	* @access public
	* @wp-hook woocommerce_after_shop_loop_item
	* @return void
	*/
	function superba_woocommerce_after_shop_loop_item() {

		global $post, $product;
		$size = sizeof( get_the_terms( $post->ID, 'product_cat' ) );
		echo $product->get_categories( ', ', '<span class="posted_in">', '</span>' );

		woocommerce_template_loop_rating();

		echo '<div class="thb-add-to-cart-wrapper">';
			WGM_Template::woocommerce_de_price_with_tax_hint_loop();
		echo "</div>";

		woocommerce_template_loop_add_to_cart();
		echo "</div>";

	}

	/**
	* Theme Superba Support: Double price on single product pages
	*
	* @access public
	* @wp-hook woocommerce_single_product_summary
	* @return void
	*/
	function superba_woocommerce_single_product_summary() {

		?>
		<div class="thb-product-header">
			<?php
				thb_pagination();
				woocommerce_breadcrumb();
				woocommerce_template_single_title();
				woocommerce_template_loop_rating();
				WGM_Template::woocommerce_de_price_with_tax_hint_single();
			?>
		</div>
		<div class="thb-product-description">
			<?php
				woocommerce_template_single_excerpt();
				woocommerce_template_single_add_to_cart();
			?>
		</div>
		<?php

	}

	/**
	* Woocommerce Subscriiption active filter wcs_new_order_created:filter_wcs_new_order_created each new Order, this new Order its same Subscription-Order, (Copy-Parent)
	* we have to edit_wp_wc_running_invoice_number, _wp_wc_running_invoice_number_date in the new Order
	*
	* @since GM 3.4.3
	* @wp-hook wcs_new_order_created
	* @param WC_Order $order
	* @return WC_Order
	*/
    public function filter_wcs_new_order_created( $order ){

        if ( ( $order->get_id() != null ) && ( $order->get_id() == $this->_new_order_create ) ) {

            $date_process= get_post_meta( $order->get_id(), '_wp_wc_running_invoice_number_date', true );

            if ( (int)$this->_wp_wc_running_invoice_number_date > (int)$date_process ) {
                $order->update_meta_data( '_wp_wc_running_invoice_number', $this->_wp_wc_running_invoice_number );
                $order->update_meta_data( '_wp_wc_running_invoice_number_date', $this->_wp_wc_running_invoice_number_date );
            }
        }

        return $order;
    }

	/**
	* Wenn new Order is created, New_order = Parent_order_subscrition, then we save data and number, (this process with German-Marker its Okay, but this will replace in the new Filter).
	*
	* @since GM 3.4.3
	* @wp-hook woocommerce_new_order
	* @param WC_Order $order
	* @return WC_Order
	*/
    public function action_woocommerce_new_order( $order ){

        $this->_new_order_create = $order;
        $this->_wp_wc_running_invoice_number = get_post_meta( $order, '_wp_wc_running_invoice_number', true );
        $this->_wp_wc_running_invoice_number_date = get_post_meta( $order, '_wp_wc_running_invoice_number_date', true );

       return $order;
    }

	/**
	* Wenn new Order is created, New_order Parent_order_subscrition,
	* @since GM 3.4.3
	* @wp-hook woocommerce_countries_inc_tax_or_vat, woocommerce_countries_ex_tax_or_vat
	* @param String $return
	* @return String
	*/
	public function dummy_remove_woo_vat_notice( $return = "" ){

        if ( $return == "" ){
            $return = " ";
        }

        return $return;
    }

    /**
	* WPML Support: Switch language of doubple-opt-in resender
	*
	* @since 3.9.2
	* @wp-hook wgm_double_opt_in_customer_registration_before_bulk_resend_email
	* @param Integer $user_id
	* @return void
	*/
    function wpml_resend_double_opt_in_before( $user_id ) {

    	global $sitepress;
    	$user_language = get_user_meta( $user_id, '_wgm_double_opt_in_activation_lang', true );
    	$user_language_array = explode( '_', $user_language );

    	if ( is_array( $user_language_array ) ) {
    		$user_language = $user_language_array[ 0 ];
    	}

    	if ( ! empty( $user_language ) ) {
    		$sitepress->switch_lang( $user_language );
    	}

    }

    /**
	* WPML Support: Switch language of doubple-opt-in resender
	*
	* @since 3.9.2
	* @wp-hook wgm_double_opt_in_customer_registration_after_bulk_resend_email
	* @param Integer $user_id
	* @return void
	*/
    function wpml_resend_double_opt_in_after( $user_id ) {

    	global $sitepress;
    	$sitepress->switch_lang( $sitepress->get_default_language() );

    }

    /**
	* WPML Support: Translate product attribute names when attributes are saved when add to cart
	*
	* @since 3.10.2
	* @param String $attribute_name
	* @param WC_Product $product
	* @return String
	*/
    public function wpml_german_market_attribute_name_add_to_cart( $attribute_name, $product ) {

    	global $woocommerce_wpml;
    	if ( $woocommerce_wpml->strings ) {
    		$attribute_name =  $woocommerce_wpml->strings->translated_attribute_label( $attribute_name, $attribute_name, $product );
    	}

    	return $attribute_name;
    }

    /**
	* WPML Support: Gateway Instructions and Titles are not translated as they should
	*
	* @since 3.9.2
	* @param String $content
	* @param WC_Order $order
	* @return Sring
	*/
    public static function wpml_repair_payment_methods( $content, $order, $args ) {
    	if ( WGM_Helper::method_exists( $order, 'get_payment_method' ) ) {

			if ( WGM_Helper::method_exists( $order, 'get_meta' ) ) {

				$order_language = $order->get_meta( 'wpml_language' );

				if ( isset( $args[ 'admin' ] ) && get_option( 'german_market_wpml_pdf_language', 'order_lang' ) != 'order_lang' ) {
					$order_language = get_option( 'german_market_wpml_pdf_language', 'order_lang' );
				}

				if ( ! empty( $order_language ) ) {

					global $sitepress;

					$default_wpml_language 	= $sitepress->get_default_language();
					$current_language 		= $sitepress->get_current_language();

					$payment_method_id 	= $order->get_payment_method();
					$payment_gateways   = WC_Payment_Gateways::instance();
					$all_gateways 		= $payment_gateways->payment_gateways();

					if ( isset( $all_gateways[ $payment_method_id ] ) ) {

						$used_gateway = $all_gateways[ $payment_method_id ];

						$gateway_method_title = $used_gateway->title;

						if ( isset( $used_gateway->instructions ) ) {
							$gateway_method_instructions = $used_gateway->instructions;
						}

						$settings = $used_gateway->settings;

						$settings_language_instructions = isset( $settings[ 'instructions' ] ) ? $settings[ 'instructions' ] : false;
						$settings_language_title 		= $settings[ 'title' ];

						if ( $settings_language_instructions ) {
							$current_language_instructions 	= apply_filters( 'wpml_translate_single_string', $settings_language_instructions, 'admin_texts_woocommerce_gateways', $payment_method_id .'_gateway_instructions', $current_language );
						}

						$current_language_title 		= apply_filters( 'wpml_translate_single_string', $settings_language_title, 'admin_texts_woocommerce_gateways', $payment_method_id .'_gateway_title', $current_language );

						if ( $settings_language_instructions ) {
							$order_language_instructions 	= apply_filters( 'wpml_translate_single_string', $settings_language_instructions, 'admin_texts_woocommerce_gateways', $payment_method_id .'_gateway_instructions', $order_language );
						}

						$order_language_title 			= apply_filters( 'wpml_translate_single_string', $settings_language_title, 'admin_texts_woocommerce_gateways', $payment_method_id .'_gateway_title', $order_language );

						if ( $settings_language_instructions ) {
							$content = str_replace( $current_language_instructions, $order_language_instructions, $content );
						}

						$content = str_replace( $current_language_title, $order_language_title, $content );

						if ( isset( $used_gateway->instructions ) ) {
							$content = str_replace( $gateway_method_instructions, $order_language_instructions, $content );
						}

						$content = str_replace( $gateway_method_title, $order_language_title, $content );

						if ( ! empty($order->get_payment_method_title() ) ) {
							$content = str_replace( $order->get_payment_method_title(), $order_language_title, $content );
						}

					}

				}

			}

		}

		return $content;

    }

    /**
	* WPML Support: Switch language of invoice in backend downloads
	*
	* @since 3.8.1
	* @access public
	* @wp-hook wcreapdf_pdf_before_create
	* @param Array $args
	* @return void
	*/
    function wpml_invoice_pdf_admin_download_switch_lang( $args ) {

    	if ( self::is_frontend_ajax() ) {
    		return;
    	}

    	if ( ! current_user_can( 'manage_woocommerce' ) ) {
    		return;
    	}

		global $sitepress;
		$order 		= $args[ 'order' ];
		$is_test 	= is_string( $args[ 'order' ] ) && $args[ 'order' ] == 'test';

		if ( ! $is_test ) {

			if ( WGM_Helper::method_exists( $order, 'get_meta' ) ) {

				$order_language = $order->get_meta( 'wpml_language' );

				if ( isset( $args[ 'admin' ] ) && get_option( 'german_market_wpml_pdf_language', 'order_lang' ) != 'order_lang' ) {
					$order_language = get_option( 'german_market_wpml_pdf_language', 'order_lang' );
				}

				if ( ! empty( $order_language ) ) {
					$sitepress->switch_lang( $order_language );
				}
			}

		}

    }

    /**
	* WPML Support: Switch language of invoice for online booking APIs
	*
	* @since 3.10.1
	* @access public
	* @wp-hook german_market_invoice_pdf_before_send_to_booking_accounts
	* @param Array $args
	* @return void
	*/
    public static function wpml_invoice_pdf_switch_lang_for_online_booking( $args ) {

    	global $sitepress;

		if ( ! $sitepress ) {
			return;
		}

		$order 		= $args[ 'order' ];
		$is_test 	= is_string( $args[ 'order' ] ) && $args[ 'order' ] == 'test';

		if ( ! $is_test ) {

			if ( WGM_Helper::method_exists( $order, 'get_meta' ) ) {

				$order_language = $order->get_meta( 'wpml_language' );

				if ( isset( $args[ 'admin' ] ) && get_option( 'german_market_wpml_pdf_language', 'order_lang' ) != 'order_lang' ) {
					$order_language = get_option( 'german_market_wpml_pdf_language', 'order_lang' );
				}

				if ( ! empty( $order_language ) ) {
					$sitepress->switch_lang( $order_language );
				}
			}

		}

    }

    /**
	* WPML Support: Reswitch language of invoice pdf in backend downloads
	*
	* @since 3.10.1
	* @access public
	* @wp-hook german_market_invoice_pdf_after_send_to_booking_accounts
	* @return void
	*/
    public static function wpml_invoice_pdf_reswitch_lang_for_online_booking() {

    	global $sitepress;

    	if ( ! $sitepress ) {
			return;
		}

    	$sitepress->switch_lang( $sitepress->get_default_language() );
    }

    /**
	* WPML Support: Reswitch language of invoice pdf in backend downloads
	*
	* @since 3.8.1
	* @access public
	* @wp-hook wp_wc_invoice_pdf_end_template
	* @return void
	*/
    function wpml_invoice_pdf_admin_download_reswitch_lang() {

    	if ( self::is_frontend_ajax() ) {
    		return;
    	}

    	if ( ! current_user_can( 'manage_woocommerce' ) ) {
    		return;
    	}

    	global $sitepress;
    	$sitepress->switch_lang( $sitepress->get_default_language() );
    }

    /**
	* WPML Support: Switch language of retoure and delivery pdf in backend downloads
	*
	* @since 3.8.1
	* @access public
	* @wp-hook wcreapdf_pdf_before_create
	* @param Array $settings
	* @param WC_Order $order
	* @return void
	*/
    function wpml_retoure_pdf_admin_download_switch_lang( $delivery_or_retoure, $order, $admin = false ) {

    	if ( self::is_frontend_ajax() ) {
    		return;
    	}

    	if ( ! current_user_can( 'manage_woocommerce' ) ) {
    		return;
    	}

    	if ( ! WGM_Helper::method_exists( $order , 'get_meta' ) ) {
    		return;
    	}

		global $sitepress;

		$order_language = $order->get_meta( 'wpml_language' );


		if ( $admin && get_option( 'german_market_wpml_pdf_language', 'order_lang' ) != 'order_lang' ) {
			$order_language = get_option( 'german_market_wpml_pdf_language', 'order_lang' );
		}

		if ( ! empty( $order_language ) ) {
			$sitepress->switch_lang( $order_language );
		}

    }

    /**
	* WPML Support: Reswitch language of retoure and delivery pdf in backend downloads
	*
	* @since 3.8.1
	* @access public
	* @wp-hook wcreapdf_pdf_after_create
	* @param Array $settings
	* @param WC_Order $order
	* @return Array
	*/
    function wpml_retoure_pdf_admin_download_reswitch_lang( $delivery_or_retoure, $order ) {

    	if ( self::is_frontend_ajax() ) {
    		return;
    	}

    	if ( ! current_user_can( 'manage_woocommerce' ) ) {
    		return;
    	}

    	global $sitepress;
    	$sitepress->switch_lang( $sitepress->get_default_language() );

    }

     /**
	* Returns true if ajax is executed from frontend
	*
	* @since 3.8.1
	* @access public
	* @return Boolean
	*/
    public static function is_frontend_ajax() {

    	$script_filename = isset( $_SERVER[ 'SCRIPT_FILENAME' ] ) ? $_SERVER[ 'SCRIPT_FILENAME' ] : '';

		//Try to figure out if frontend AJAX request... If we are DOING_AJAX; let's look closer
		if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {

	    	$ref = '';

			if ( ! empty( $_REQUEST[ '_wp_http_referer' ] ) ) {
				$ref = wp_unslash( $_REQUEST[ '_wp_http_referer' ] );
			} elseif ( ! empty( $_SERVER[ 'HTTP_REFERER' ] ) ) {
				$ref = wp_unslash( $_SERVER[ 'HTTP_REFERER' ] );
			}

			// If referer does not contain admin URL and we are using the admin-ajax.php endpoint, this is likely a frontend AJAX request
			if ( ( ( strpos( $ref, admin_url() ) === false ) && ( basename( $script_filename ) === 'admin-ajax.php' ) ) ) {
			  return true;
			}
		}

		return false;

	}

	/**
	* WooCommerce Subscriptions Support: Don't copy invoice number, invoice date, saved invoice pdf content or due date form first order to subscription object
	*
	* @access public
	* @last-updated: 3.10.6.0.1
	* @wp-hook wcs_renewal_order_meta
	* @param Array $meta
	* @param WC_Order $to_order
	* @param WC_Order $from_order
	* @return Array
	*/
	function subscriptions_gm_dont_copy_meta( $meta, $to_order, $from_order ) {

		$new_meta = array();

		foreach ( $meta as $meta_item ) {

			$key = $meta_item[ 'meta_key' ];

			if ( $key == '_wp_wc_running_invoice_number' || $key == '_wp_wc_running_invoice_number_date' || $key == '_wp_wc_invoice_pdf_saved_html' || $key == '_lexoffice_woocomerce_has_transmission' || $key == '_sevdesk_woocomerce_has_transmission' ) {
				continue;
			}

			if ( $key == '_wgm_due_date' ) {

				if ( get_option( 'woocommerce_de_due_date', 'off' ) == 'on' ) {

					$wgm_due_date = WGM_Due_date::get_instance();
    				$wgm_due_date->save_due_date_in_order( $to_order->get_id(), false, $from_order->get_payment_method() );

				}

				continue;

			}

			$new_meta[] = $meta_item;

		}

		return $new_meta;
	}

	/**
	* WooCommerce Subscriptions Support: Email Attachments
	*
	* @access public
	* @wp-hook gm_invoice_pdf_email_settings
	* @wp-hook gm_invoice_pdf_email_settings_additonal_pdfs
	* @param Array $options
	* @return Array
	*/
	function subscriptions_gm_invoice_pdf_email_settings( $options ) {

		$prefix = current_filter() == 'gm_invoice_pdf_email_settings_additonal_pdfs' ? '_add_pdfs' : '';

		$options[] = array( 'title' => __( 'WooCommerce Subscriptions Support', 'woocommerce-german-market' ), 'type' => 'title','desc' => '', 'id' => 'wp_wc_invoice_pdf_emails_subcriptions' . $prefix );

		$options[] = array(
			'name'		=> __( 'New Renewal Order', 'woocommerce-german-market' ),
			'id'   		=> 'wp_wc_invoice_pdf_emails_new_renewal_order' . $prefix,
			'type' 		=> 'wgm_ui_checkbox',
			'default'  	=> 'off',
		);

		$options[] = array(
			'name'		=> __( 'Processing Renewal Order', 'woocommerce-german-market' ),
			'id'   		=> 'wp_wc_invoice_pdf_emails_customer_processing_renewal_order' . $prefix,
			'type' 		=> 'wgm_ui_checkbox',
			'default'  	=> 'off',
		);

		$options[] = array(
			'name'		=> __( 'Complete Renewal Order', 'woocommerce-german-market' ),
			'id'   		=> 'wp_wc_invoice_pdf_emails_customer_completed_renewal_order' . $prefix,
			'type' 		=> 'wgm_ui_checkbox',
			'default'  	=> 'off',
		);

		$options[] = array(
			'name'		=> __( 'Customer Complete Switch Order', 'woocommerce-german-market' ),
			'id'   		=> 'wp_wc_invoice_pdf_emails_customer_completed_switch_order' . $prefix,
			'type' 		=> 'wgm_ui_checkbox',
			'default'  	=> 'off',
		);

		$options[] = array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_emails_subcriptions' . $prefix );

		return $options;
	}

	/**
	*  WooCommerce Subscriptions Support: Email Attachments for Retoure PDF
	*
	* @access public
	* @since 3.5.6
	* @wp-hook wcreapdf_email_options_after_sectioned
	* @param Array $options
	* @return Array
	*/
	function subscriptions_gm_retoure_pdf_email_settings( $options ) {


		$options[] = array( 'title' => __( 'WooCommerce Subscriptions Support', 'woocommerce-german-market' ), 'type' => 'title','desc' => '', 'id' => 'wp_wc_retoure_pdf_emails_subcriptions' );

		$options[] = array(
			'name'		=> __( 'New Renewal Order', 'woocommerce-german-market' ),
			'id'   		=> WCREAPDF_Helper::get_wcreapdf_optionname( 'new_renewal_order' ),
			'type' 		=> 'wgm_ui_checkbox',
			'default'  	=> 'off',
		);

		$options[] = array(
			'name'		=> __( 'Processing Renewal Order', 'woocommerce-german-market' ),
			'id'   		=> WCREAPDF_Helper::get_wcreapdf_optionname( 'customer_processing_renewal_order' ),
			'type' 		=> 'wgm_ui_checkbox',
			'default'  	=> 'off',
		);

		$options[] = array(
			'name'		=> __( 'Complete Renewal Order', 'woocommerce-german-market' ),
			'id'   		=> WCREAPDF_Helper::get_wcreapdf_optionname( 'customer_completed_renewal_order' ),
			'type' 		=> 'wgm_ui_checkbox',
			'default'  	=> 'off',
		);

		$options[] = array(
			'name'		=> __( 'Customer Complete Switch Order', 'woocommerce-german-market' ),
			'id'   		=> WCREAPDF_Helper::get_wcreapdf_optionname( 'customer_completed_switch_order' ),
			'type' 		=> 'wgm_ui_checkbox',
			'default'  	=> 'off',
		);

		$options[] = array( 'type' => 'sectionend', 'id' => 'wp_wc_retoure_pdf_emails_subcriptions' );

		return $options;
	}

	/**
	* WooCommerce Subscriptions Support: Email Attachments
	*
	* @access public
	* @wp-hook wp_wc_inovice_pdf_allowed_stati
	* @wp-hook wp_wc_inovice_pdf_allowed_stati_additional_mals
	* @param Array allowed_stati
	* @return Array
	*/
	function subscriptions_gm_allowed_stati_additional_mals( $allowed_stati ) {

		$allowed_stati[] = 'new_renewal_order';
		$allowed_stati[] = 'customer_processing_renewal_order';
		$allowed_stati[] = 'customer_completed_renewal_order';
		$allowed_stati[] = 'customer_completed_switch_order';

		return $allowed_stati;
	}

	/**
	* WooCommerce Subscriptions Support: BCC / CC 
	*
	* @since 3.10.4.1
	* @access public
	* @wp-hook german_market_options_bcc_emails
	* @param Array options
	* @return Array
	*/
	public function subscriptions_gm_bbc_cc_emails( $options ) {

		$last_key_sectioned = $options[ 'last_key_sectioned' ];
		unset( $options[ 'last_key_sectioned' ] );

		$options[] = array(
			'name'		=> __( 'New Renewal Order', 'woocommerce-german-market' ),
			'id'   		=> 'wgm_email_cc_bcc_new_renewal_order',
			'type' 		=> 'wgm_ui_checkbox',
			'default'  	=> 'off',
		);

		$options[] = array(
			'name'		=> __( 'Processing Renewal Order', 'woocommerce-german-market' ),
			'id'   		=> 'wgm_email_cc_bcc_customer_processing_renewal_order',
			'type' 		=> 'wgm_ui_checkbox',
			'default'  	=> 'off',
		);

		$options[] = array(
			'name'		=> __( 'Complete Renewal Order', 'woocommerce-german-market' ),
			'id'   		=> 'wgm_email_cc_bcc_customer_completed_renewal_order',
			'type' 		=> 'wgm_ui_checkbox',
			'default'  	=> 'off',
		);

		$options[] = array(
			'name'		=> __( 'Customer Complete Switch Order', 'woocommerce-german-market' ),
			'id'   		=> 'wgm_email_cc_bcc_customer_completed_switch_order',
			'type' 		=> 'wgm_ui_checkbox',
			'default'  	=> 'off',
		);

		$options[ 'last_key_sectioned' ] = $last_key_sectioned;

		return $options;
	}

	/**
	* WooCommerce Subscriptions Support: Email Attachments in Add-Ons
	*
	* @access public
	* @wp-hook gm_emails_in_add_ons
	* @param Array allowed_stati
	* @return Array
	*/
	function subscriptions_gm_emails_in_add_ons( $emails ) {

		$emails[ 'new_renewal_order' ] 					= __( 'New Renewal Order', 'woocommerce-german-market' );
		$emails[ 'customer_processing_renewal_order' ]	= __( 'Processing Renewal Order', 'woocommerce-german-market' );
		$emails[ 'customer_completed_renewal_order' ]	= __( 'Complete Renewal Order', 'woocommerce-german-market' );

		return $emails;
	}

	/**
	* WooCommerce Subscriptions Support: Recurring Totals
	*
	* @access public
	* @wp-hook german_market_after_frontend_init
	*/
	function subscriptions() {
		remove_filter( 'woocommerce_cart_totals_order_total_html',	array( 'WGM_Template', 'woocommerce_cart_totals_excl_tax_string' ) );
	}

	/**
	* WPML Support: Translate WooCommerce Tax Rates for WPML
	*
	* @access public
	* @wp-hook woocommerce_find_rates
	* @param Array $matched_tax_rates
	* @return Array
	*/
	function translate_woocommerce_find_rates( $matched_tax_rates ) {

		global $sitepress;

        foreach( $matched_tax_rates as &$rate ) {

                if ( $rate[ 'label' ] ) {
                    
                    $has_translation = null;
                	$auto_register = true;
					$lang = $sitepress->get_current_language();
                    
                    $rate[ 'label' ] = icl_t( 'German Market: WooCommerce Tax Rate', 'tax rate label: ' . $rate[ 'label' ], $rate[ 'label' ], $has_translation, $auto_register, $lang );
                }

                unset($rate);

        }

        reset($matched_tax_rates);

        return $matched_tax_rates;

	}

	/**
	* WPML Support: Translate Empty WooCommerce Checkout Strings
	*
	* @access public
	* @wp-hook option_{option}
	* @param String $value
	* @param String $option
	* @return String
	*/
	function translate_empty_translate_woocommerce_checkout_options( $default, $option, $passed_default ) {

		global $sitepress;
		$default_lang = $sitepress->get_default_language();

		$default_sentence 	= $this->translate_woocommerce_checkout_options( $default, $option, $default_lang );
		$translation 		= $this->translate_woocommerce_checkout_options( $default, $option );

		if ( $translation != $default_sentence ) {
			$default = $translation;
		}

		return $default;
	}

	/**
	* WPML Support: Translate WooCommerce Checkout Strings
	*
	* @access public
	* @wp-hook option_{option}
	* @param String $value
	* @param String $option
	* @return String
	*/
	function translate_woocommerce_checkout_options( $value, $option, $translation_lang = false ) {

		global $sitepress;
		$lang = $sitepress->get_current_language();
		$default_lang = $sitepress->get_default_language();

		if ( $lang == $default_lang ) {
			return $value;
		}

		if ( $translation_lang ) {
			$lang = $translation_lang;
		}

		if ( str_replace( 'wp_wc_invoice_pdf_', '', $option ) != $option ) {
			$value = apply_filters( 'wpml_translate_single_string', $value, 'German Market: Invoice PDF', $option, $lang );

		} else if ( str_replace( 'wp_wc_running_invoice_', '', $option ) != $option ) {
			$value = apply_filters( 'wpml_translate_single_string', $value, 'German Market: Running Invoice Number', $option, $lang );

		} else if ( ( str_replace( 'woocomerce_wcreapdf_wgm_', '', $option ) != $option ) || ( str_replace( 'woocommerce_wcreapdf_wgm_', '', $option ) != $option  ) ) {
			$value = apply_filters( 'wpml_translate_single_string', $value, 'German Market: Return Delivery Note', $option, $lang );

		} else {

			$value = apply_filters( 'wpml_translate_single_string', $value, 'German Market: Checkout Option', $option, $lang );
		}

		return $value;

	}

	/**
	* Polylang Support: Translate WooCommerce Checkout Strings
	*
	* @access public
	* @wp-hook option_{option}
	* @param String $value
	* @param String $option
	* @return String
	*/
	function translate_woocommerce_checkout_options_polylang( $value, $option ) {

		$value = pll__( $value );
		return $value;

	}

	/**
	* WPMP Support: Translate Tax Labels for order items
	*
	* @access public
	* @wp-hook option_{wgm_translate_tax_label}
	* @param String $tax_label
	* @return String
	*/
	function translate_tax_label( $tax_label ) {

		// WPML
		if ( function_exists( 'icl_register_string' ) && function_exists( 'icl_t' ) && function_exists( 'icl_st_is_registered_string' ) ) {
			$tax_label = icl_t( 'German Market: WooCommerce Tax Rate', 'tax rate label: ' . $tax_label, $tax_label );
		}

		return $tax_label;
	}

	/**
	* Custom Emails: Email Attachments
	*
	* @access public
	* @since 3.5.6
	* @wp-hook gm_invoice_pdf_email_settings
	* @wp-hook gm_invoice_pdf_email_settings_additonal_pdfs
	* @param Array $options
	* @return Array
	*/
	function custom_email_status_gm_invoice_pdf_email_settings( $options ) {

		$custom_mails = $this->get_custom_emails();

		if ( ! empty( $custom_mails ) ) {

			$prefix = current_filter() == 'gm_invoice_pdf_email_settings_additonal_pdfs' ? '_add_pdfs' : '';

			$options[] = array( 'title' => __( 'Custom Emails', 'woocommerce-german-market' ), 'type' => 'title','desc' => '', 'id' => 'wp_wc_invoice_pdf_emails_custom' . $prefix );

			foreach ( $custom_mails as $custom_mail ) {

				$options[] = array(
					'name'			=> $custom_mail[ 'title' ],
					'desc_tip'		=> $custom_mail[ 'description' ],
					'id'   			=> 'wp_wc_invoice_pdf_emails_' . $custom_mail[ 'id' ] . $prefix,
					'type' 			=> 'wgm_ui_checkbox',
					'default'  		=> 'off',
				);
			}

			$options[] = array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_emails_custom' . $prefix );

		}

		return $options;
	}

	/**
	* Custom Emails: Email Attachments for Retoure PDF
	*
	* @access public
	* @since 3.5.6
	* @wp-hook wcreapdf_email_options_after_sectioned
	* @param Array $options
	* @return Array
	*/
	function custom_email_status_gm_retoure_pdf_email_settings( $options ){

		$custom_mails = $this->get_custom_emails();

		if ( ! empty( $custom_mails ) ) {

			$options[] = array( 'title' => __( 'Custom Emails', 'woocommerce-german-market' ), 'type' => 'title','desc' => '', 'id' => 'wcreapdf_email_custom' );

			foreach ( $custom_mails as $custom_mail ) {

				$options[] = array(
					'name'			=> $custom_mail[ 'title' ],
					'desc_tip'		=> $custom_mail[ 'description' ],
					'id'   			=>  WCREAPDF_Helper::get_wcreapdf_optionname( $custom_mail[ 'id' ] ),
					'type' 			=> 'wgm_ui_checkbox',
					'default'  		=> 'off',
				);
			}

			$options[] = array( 'type' => 'sectionend', 'id' => 'wcreapdf_email_custom' );

		}

		return $options;
	}

	/**
	* Get Custom Emails
	*
	* @access private
	* @since 3.5.6
	* @return Array
	*/
	private function get_custom_emails() {

		$all_mails 		= WC()->mailer()->get_emails();
		$custom_mails 	= array();

		foreach ( $all_mails as $key => $a_mail ) {

			if ( substr( $key, 0, 3 ) == 'WC_' || substr( $key, 0, 4 ) == 'WCS_' ) {
				continue;
			}

			$custom_mails[ $key ] = array(
				'title'			=> $a_mail->title,
				'description'	=> $a_mail->description,
				'id'			=> $a_mail->id,
			);
		}

		return $custom_mails;

	}

	/**
	* Custom Emails - Attachments
	*
	* @access public
	* @since 3.5.6
	* @wp-hook wp_wc_inovice_pdf_allowed_stati
	* @wp-hook wp_wc_inovice_pdf_allowed_stati_additional_mals
	* @param Array allowed_stati
	* @return Array
	*/
	function custom_email_status_gm_allowed_stati_additional_mals( $allowed_stati ) {

		$custom_mails = $this->get_custom_emails();

		foreach ( $custom_mails as $a_mail ) {
			$allowed_stati[] = $a_mail[ 'id' ];
		}

		return $allowed_stati;
	}

	/**
	* Custom Emails: Email Attachments in Add-Ons
	*
	* @access public
	* @since 3.5.6
	* @wp-hook gm_emails_in_add_ons
	* @param Array allowed_stati
	* @return Array
	*/
	function custom_email_status_gm_emails_in_add_ons( $emails ) {

		$custom_mails = $this->get_custom_emails();

		foreach ( $custom_mails as $a_mail ) {
			$emails[ $a_mail[ 'id' ] ] 	= $a_mail[ 'title' ];
		}

		return $emails;
	}

}
