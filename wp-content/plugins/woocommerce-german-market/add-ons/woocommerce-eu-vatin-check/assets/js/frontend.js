/**
 * Feature Name: Frontend Scripts
 * Version:      1.0
 * Author:       MarketPress
 * Author URI:   http://marketpress.com/
 */

/** Menu **/
(
	function( $ ) {
		var wcvat_frontend_scripts = {

			_valid: false,

			// Pseudo-Constructor of this class
			init  : function() {

				wcvat_frontend_scripts.hide_vatin_field();

				// check if we have a billing_company input field
				if ( $( '#billing_company' ).length ) {
					wcvat_frontend_scripts.check_billing_company_input();
				}

				// ajaxify billing VAT field
				if ( $( '#billing_vat_field' ).length ) {
					wcvat_frontend_scripts.ajax_check_vat_field();
				}

				if ( $( '#billing_country' ).length ) {
					wcvat_frontend_scripts.billing_country_handle();
				}
			},

			billing_country_handle : function() {

				$( '#billing_country' ).ready( function(){
					$( '#billing_country' ).trigger( 'change' );
				});

				$( document ).on( 'change', '#billing_country', function( e ) {

					if ( wcvat_script_vars.base_country_hide || wcvat_script_vars.base_country_hide == 1 ) {

						if ( ( ( wcvat_script_vars.base_country == $( '#billing_country' ).val() ) && ( ! wcvat_script_vars.show_for_basecountry_hide_eu_countries ) ) || $( '#billing_country' ).val() == '' )  {

							$( '#billing_vat' ).val( '' ).trigger( 'blur' )
							$( '#billing_vat_field' ).hide();
						
						} else if ( ! wcvat_script_vars.eu_countries.includes( $( '#billing_country' ).val() ) ) {

							$( '#billing_vat' ).val( '' ).trigger( 'blur' )
							$( '#billing_vat_field' ).hide();

						} else {

							$( '#billing_vat_field' ).show();

						}
						
					}
					
				});

			},

			hide_vatin_field           : function() {
				// $( '#billing_vat_field' ).hide();
			},

			// AJAX check for the VAT-Field
			ajax_check_vat_field       : function() {
				var lock = false;

				$( 'input#billing_vat' ).ready( function(){
					
					if ( $( 'input#billing_vat' ).val() != '' ) {
						$( 'input#billing_vat' ).trigger( 'blur' );
					}

				});

				$( document ).on( 'blur', 'input#billing_vat', function( e ) {
					
					if ( lock == true ) {
						return false;
					}

					lock = true;

					// set vat
					var vat = $( this ).val();
					var vat_field = $( this );

					wcvat_frontend_scripts.clean_up_badges();
					vat_field.after( wcvat_script_vars.spinner );

					// set the post vars
					var post_vars = {
						action : 'wcvat_check_vat',
						vat    : vat,
						country: $( '#billing_country' ).val()
					};

					$.ajax( {
						data    : post_vars,
						url     : wcvat_script_vars.ajaxurl,
						async   : true,
						dataType: 'json'
					} )

						.always( function() {
							//clean up
							wcvat_frontend_scripts.clean_up_badges();
						} )

						.done( function( response ) {

							if ( response ) {
								if ( response.success === false ) {
									
									if ( vat != '' ) {
										vat_field.addClass( 'error' );
										vat_field.after( wcvat_script_vars.error_badge );
									}

								} else {
									$( '.error-badge' ).remove();
									vat_field.after( wcvat_script_vars.correct_badge );
									wcvat_frontend_scripts._valid = true;
								}
							}
						} )

						.always( function() {
							lock = false;
							
							if ( wcvat_script_vars.trigger_update_checkout ) {
								$( 'body' ).trigger( 'update_checkout' );
							}
							
						} );
				} );
			},

			// this function checks if there is an input value
			// at the 'billing_company' field and if it is so
			// we'll display the
			check_billing_company_input: function() {

				// initial check
				var input_length = $( '#billing_company' ).val().length;
				if ( input_length >= 1 ) {
					// show vat input if not showen yet
					//if ( !$( '#billing_vat_field' ).is( ':visible' ) ) {
					//	$( '#billing_vat_field' ).slideDown( 'fast' );
					//}
				}

				// keyup check
				$( document ).on( 'keyup', '#billing_company', function() {

					var input_length = $( this ).val().length;
					if ( input_length >= 1 ) {
						// show vat input if not showen yet
						//if ( !$( '#billing_vat_field' ).is( ':visible' ) ) {
							//$( '#billing_vat_field' ).slideDown( 'fast' );
						//}
					} else {
						// hide vat input if not hidden
						//if ( $( '#billing_vat_field' ).is( ':visible' ) ) {
						//	$( '#billing_vat' ).val( '' );
						//	$( '#billing_vat_field' ).slideUp( 'fast' );
						//}
					}
				} );
			},

			clean_up_badges: function() {
				$( '.error-badge' ).remove();
				$( '.spinner-badge' ).remove();
				$( '.correct-badge' ).remove();
				$( '.spinner-badge' ).remove();
			}
		};

		$( document ).ready( wcvat_frontend_scripts.init );
	}
)( jQuery );
