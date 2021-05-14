if ( german_market_jquery_no_conflict.no_conflict == 'yes' ) {
    jQuery.noConflict();
}

( function( $ ) {

    woocommerce_de = {

        init: function () {

            this.scale_unit_hint();
            this.show_and_hide_panels();

            $('input#_digital, input.variable_is_downloadable').change(function(){
                woocommerce_de.show_and_hide_panels();
            });

            $( document ).on( 'woocommerce_variations_loaded', function() {
               $( '#variable_product_options' ).on( 'change', 'input.variable_is_downloadable, input.variable_is_digital', function () {
                    var variation_div = $( this ).closest( '.woocommerce_variable_attributes' );
                    woocommerce_de.show_and_hide_variable_requirements( variation_div );
                } );
            });

            this.video();
            this.update_screen();
            this.email_attachments();

            jQuery( '#de_shop_emails_file_attachments_nr' ).change( function() {
                woocommerce_de.email_attachments();
            });

            this.upload_attachments();
            this.remove_attachments();
            this.ui_tooltips();
            this.wgm_ui_checkbox_reader();
            this.variation_settings();
            this.ppu_wc_weights();
            this.sevdesk();
            this.legal_texts();
            this.doubleoptin_autodelete();
            this.fic();
            this.delivery_time_pdfs();
            this.colorpick();

        },

        colorpick: function() {

            $( '.german-market .colorpick' )

                .iris({
                    change: function( event, ui ) {
                        $( this ).parent().find( '.colorpickpreview' ).css({ backgroundColor: ui.color.toString() });
                    },
                    hide: true,
                    border: true
                })

                .on( 'click focus', function( event ) {
                    event.stopPropagation();
                    $( '.iris-picker' ).hide();
                    $( this ).closest( 'td' ).find( '.iris-picker' ).show();
                    $( this ).data( 'original-value', $( this ).val() );
                })

                .on( 'change', function() {
                    if ( $( this ).is( '.iris-error' ) ) {
                        var original_value = $( this ).data( 'original-value' );

                        if ( original_value.match( /^\#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/ ) ) {
                            $( this ).val( $( this ).data( 'original-value' ) ).change();
                        } else {
                            $( this ).val( '' ).change();
                        }
                    }
                });

            $( 'body .german-market' ).on( 'click', function() {
                $( '.iris-picker' ).hide();
            });

        },

        delivery_time_pdfs: function() {

            if ( jQuery( '#woocommerce_de_show_delivery_time_order_summary' ).length ) {

                jQuery( '#woocommerce_de_show_delivery_time_order_summary' ).ready( function(){
                    woocommerce_de.delivery_time_pdfs_callback();
                });

                jQuery( '#woocommerce_de_show_delivery_time_order_summary' ).change( function() {
                    woocommerce_de.delivery_time_pdfs_callback();
                });

            }

        },

        delivery_time_pdfs_callback: function() {

            var option_value = jQuery( '#woocommerce_de_show_delivery_time_order_summary' ).is( ':checked' );

            if ( ! option_value ) {
                jQuery( '#woocommerce_de_show_delivery_time_invoice_pdf' ).parent().parent().parent().parent().hide();
                jQuery( '#woocommerce_de_show_delivery_time_retoure_pdf' ).parent().parent().parent().parent().hide();
                jQuery( '#woocommerce_de_show_delivery_time_delivery_pdf' ).parent().parent().parent().parent().hide();
            } else {
                jQuery( '#woocommerce_de_show_delivery_time_invoice_pdf' ).parent().parent().parent().parent().show();
                jQuery( '#woocommerce_de_show_delivery_time_retoure_pdf' ).parent().parent().parent().parent().show();
                jQuery( '#woocommerce_de_show_delivery_time_delivery_pdf' ).parent().parent().parent().parent().show();
            }

        },

        fic: function() {

            jQuery( '.gm-fic-show-food-data' ).click( function() {
                jQuery( this ).hide();
                jQuery( this ).parent().find( '.gm-fic-hide-food-data' ).show();
                jQuery( this ).parent().parent().find( '.gm-fic-data' ).show();
            });

            jQuery( '.gm-fic-hide-food-data' ).click( function() {
                jQuery( this ).hide();
                jQuery( this ).parent().find( '.gm-fic-show-food-data' ).show();
                jQuery( this ).parent().parent().find( '.gm-fic-data' ).hide();
            });

            jQuery( document ).on( 'woocommerce_variations_loaded', function( event ) {
                
                jQuery( '.gm-fic-show-food-data-variation' ).click( function() {
                    jQuery( this ).hide();
                    jQuery( this ).parent().find( '.gm-fic-hide-food-data-variation' ).show();
                    jQuery( this ).parent().parent().find( '.gm-fic-data' ).show();
                });

                jQuery( '.gm-fic-hide-food-data-variation' ).click( function() {
                    jQuery( this ).hide();
                    jQuery( this ).parent().find( '.gm-fic-show-food-data-variation' ).show();
                    jQuery( this ).parent().parent().find( '.gm-fic-data' ).hide();
                });

            }); 

        },

        legal_texts: function() {

            jQuery( '.german-market .copy-to-clipboard.html' ).click( function() {
                
                jQuery( this ).parent().find( '.copied-success' ).slideDown();

                var text_to_copy = jQuery( this ).parent().parent().find( '.legal-text-contents-html' ).html().trim();
                var $temp = jQuery( '<textarea>' );
                jQuery( "body" ).append( $temp );
                $temp.val( text_to_copy ).select();
                document.execCommand( "copy" );
                $temp.remove();

                jQuery( this ).parent().find( '.copied-success' ).delay( 4000 ).slideUp();
                
            });

        },

        sevdesk: function() {

            if ( jQuery( '.sevdesk_select_booking_account' ).length ) {
                jQuery( '.sevdesk_select_booking_account' ).selectWoo();
            }

        },

        doubleoptin_autodelete: function() {

            if ( jQuery( '#wgm_double_opt_in_customer_registration_management' ).length ) {
                
                jQuery( '#wgm_double_opt_in_customer_registration_management' ).ready( function(){
                    woocommerce_de.doubleoptin_management_callback();
                });

                jQuery( '#wgm_double_opt_in_customer_registration_management' ).change( function() {
                    woocommerce_de.doubleoptin_management_callback();
                });
            }

            if ( jQuery( '#wgm_double_opt_in_customer_registration_autodelete' ).length ) {
                
                jQuery( '#wgm_double_opt_in_customer_registration_autodelete' ).ready( function(){
                    woocommerce_de.doubleoptin_autodelete_callback();
                });

                jQuery( '#wgm_double_opt_in_customer_registration_autodelete' ).change( function() {
                    woocommerce_de.doubleoptin_autodelete_callback();
                });
            }

        },

        doubleoptin_management_callback: function() {

            var option_value = jQuery( '#wgm_double_opt_in_customer_registration_management' ).is( ':checked' );

            if ( ! option_value ) {
                jQuery( '#wgm_double_opt_in_customer_registration_autodelete' ).parent().parent().parent().parent().hide();
                jQuery( '#wgm_double_opt_in_customer_registration_autodelete' ).prop( "checked", false );
                this.doubleoptin_autodelete_callback();
            } else {
                jQuery( '#wgm_double_opt_in_customer_registration_autodelete' ).parent().parent().parent().parent().show();
            }

        },

        doubleoptin_autodelete_callback: function() {

            var option_value = jQuery( '#wgm_double_opt_in_customer_registration_autodelete' ).is( ':checked' );

            if ( ! option_value ) {

                jQuery( '#wgm_double_opt_in_customer_registration_autodelete_days' ).parent().parent().hide();
                jQuery( '#wgm_double_opt_in_customer_registration_autodelete_extratext' ).parent().parent().hide();
            
            } else {

                jQuery( '#wgm_double_opt_in_customer_registration_autodelete_days' ).parent().parent().show();
                jQuery( '#wgm_double_opt_in_customer_registration_autodelete_extratext' ).parent().parent().show();

            }

        },

        ppu_wc_weights: function() {

            if ( jQuery( '#woocommerce_de_automatic_calculation_ppu' ).length ) {
                
                jQuery( '#woocommerce_de_automatic_calculation_ppu' ).ready( function(){
                    woocommerce_de.ppu_wc_weights_callback();
                });

                jQuery( '#woocommerce_de_automatic_calculation_ppu' ).change( function() {
                    woocommerce_de.ppu_wc_weights_callback();
                });
            }

        },

        ppu_wc_weights_callback: function() {

            var option_value = jQuery( '#woocommerce_de_automatic_calculation_ppu' ).is( ':checked' );

            if ( ! option_value ) {

                jQuery( '#woocommerce_de_automatic_calculation_use_wc_weight' ).parent().parent().parent().parent().hide();
                jQuery( '#woocommerce_de_automatic_calculation_use_wc_weight_mult' ).parent().parent().hide();
                jQuery( '#woocommerce_de_automatic_calculation_use_wc_weight_scale_unit' ).parent().parent().hide();
                jQuery( '#woocommerce_de_ppu_outpout_format_prefix' ).parent().parent().hide();
            
            } else {

                jQuery( '#woocommerce_de_automatic_calculation_use_wc_weight' ).parent().parent().parent().parent().show();
                jQuery( '#woocommerce_de_automatic_calculation_use_wc_weight_mult' ).parent().parent().show();
                jQuery( '#woocommerce_de_automatic_calculation_use_wc_weight_scale_unit' ).parent().parent().show();
                jQuery( '#woocommerce_de_ppu_outpout_format_prefix' ).parent().parent().show();
            }

        },

        wgm_ui_checkbox_reader: function() {

            var do_nothing = false;

            jQuery( '.gm-ui-checkbox.switcher-german-market' ).click( function() {

                if ( ! jQuery( this ).hasClass( 'clickable' ) ) {
                    return;
                }

                jQuery( this ).parent().find( '.gm-ui-checkbox.switcher-german-market' ).toggleClass( 'active' );
                jQuery( this ).parent().find( '.gm-ui-checkbox.switcher-german-market' ).toggleClass( 'clickable' );
                do_nothing = true;
                jQuery( this ).parent().parent().find( '.slider' ).trigger( 'click' );
                do_nothing = false;
            });

            jQuery( '.gm-slider' ).click( function() {

                if ( ! do_nothing ) {
                     jQuery( this ).parent().parent().find( '.gm-ui-checkbox.switcher-german-market' ).toggleClass( 'active' );
                     jQuery( this ).parent().parent().find( '.gm-ui-checkbox.switcher-german-market' ).toggleClass( 'clickable' );
                }

            });


        },

        variation_settings: function() {

             jQuery(document).on('woocommerce_variations_loaded', function(event) {
               
               // PPU
               jQuery( '.variable_used_setting_ppu' ).change( function() {
                    
                    var loop  = jQuery( this ).attr( 'data-loop' );

                    if ( jQuery( this ).val() == -1 || jQuery( this ).val() == "-1" ) {
                      
                        jQuery( '.gm_ppu_auot_calc_parent_special_' + loop ).hide();

                    } else {

                        jQuery( '.gm_ppu_auot_calc_parent_special_' + loop ).show();

                    }

                });

               // Shipping Information
               jQuery( '._variable_used_setting_shipping_info' ).change( function() {
                    
                    var loop  = jQuery( this ).attr( 'data-loop' );

                    if ( jQuery( this ).val() == -1 || jQuery( this ).val() == "-1" ) {
                      
                        jQuery( '.gm_shipping_info_special_' + loop ).hide();

                    } else {

                        jQuery( '.gm_shipping_info_special_' + loop ).show();

                    }

                });

               // Age Rating
               jQuery( '._v_used_setting_age_rating' ).change( function() {
                    
                    var loop  = jQuery( this ).attr( 'data-loop' );

                    if ( jQuery( this ).val() == -1 || jQuery( this ).val() == "-1" ) {
                      
                        jQuery( '.gm_age_rating_parent_special' + loop ).hide();

                    } else {

                        jQuery( '.gm_age_rating_parent_special' + loop ).show();

                    }

                });

            });

        },

        ui_tooltips: function() {


            var tiptip_args = {
                'attribute': 'data-tip',
                'fadeIn': 50,
                'fadeOut': 50,
                'delay': 200
            };

            
            $( '.german-market-main-menu .tips, .german-market-main-menu .help_tip, .german-market-main-menu .woocommerce-help-tip' ).tipTip( tiptip_args );

        },

        upload_attachments: function() {

            jQuery( '.de_shop_emails_file_attachments_upload_button' ).click( function() {

                var frame;
                var this_id      = ( jQuery( this ).attr( 'id' ) );
                var formfield_id = this_id.replace( 'de_shop_emails_file_attachments_upload_button_', 'de_shop_emails_file_attachment_' );

               // If the media frame already exists, reopen it.
                if ( frame ) {
                  frame.open();
                  return;
                }
                
                // Create a new media frame
                frame = wp.media({
                  multiple: false  // Set to true to allow multiple files to be selected
                });

                // When an image is selected in the media frame...
                frame.on( 'select', function() {
                  
                  // Get media attachment details from the frame state
                  var attachment = frame.state().get( 'selection' ).first().toJSON();
                  jQuery( '#' + formfield_id ).val( attachment.url );

                });

                // Finally, open the modal on click
                frame.open();
                
            });

        },

        remove_attachments: function() {

            jQuery( '.de_shop_emails_file_attachments_remove_button' ).click( function() {

                var this_id = ( jQuery(this).attr( 'id' ) );
                var formfield_id = this_id.replace( 'de_shop_emails_file_attachments_remove_button_', 'de_shop_emails_file_attachment_' );
                jQuery( '#' + formfield_id ).val( '' );

            });

        },

        email_attachments: function() {

            if ( jQuery( '#de_shop_emails_file_attachments_nr' ).length ) {

                var number_of_attachments = jQuery( '#de_shop_emails_file_attachments_nr' ).val();

                jQuery( '.de_shop_emails_file_attachment' ).each( function() {

                    jQuery( this ).parents( 'tr' ).hide();

                });

                for ( var i = 1; i <= number_of_attachments; i++ ) {

                    var id_of_the_element = '#de_shop_emails_file_attachment_' + i;
                    jQuery( id_of_the_element ).parents( 'tr' ).show();

                }
            }

        },

        show_and_hide_variable_requirements: function( variation_div ) {
            
            var digital_checkbox = $( variation_div ).find( '.variable_is_digital' );
            var downloadable_checkbox = $( variation_div ).find( '.variable_is_downloadable' );
            var is_variable_digital = $( digital_checkbox ).prop( 'checked' );
            var is_variable_downloadable = $( downloadable_checkbox ).prop( 'checked' );
            var is_variable_digital_or_downloadable = is_variable_digital || is_variable_downloadable;

            if ( is_variable_digital_or_downloadable ) {
                $( variation_div ).find( '.show_if_variation_downloadable_or_digital' ).show();
            } else {
                $( variation_div ).find( '.show_if_variation_downloadable_or_digital' ).hide();
            }

        },

        show_and_hide_panels: function() {
            var is_digital      = $('input#_digital:checked').length
            var is_variable_downloadable = $('input.variable_is_downloadable:checked').length;

            $('.show_if_digital').hide();
            $('.show_if_variation_is_downloadable').hide();

            if( is_digital ) {
                $('.show_if_digital').show();
            }

            if( is_variable_downloadable ) {
                $('.show_if_variation_is_downloadable').show();
            }


            $('input#_manage_stock').change();
        },

        scale_unit_hint: function () {

            if ( $( '#woocommerce_attributes .toolbar' ).length > 1)
                $( '#woocommerce_attributes .toolbar' )
                    .last()
                    .append( woocommerce_product_attributes_msg.msg );
        },

        update_screen: function() {

            $( '#update-plugins-table' ).ready( function() {
                $( this ).find( 'strong' ).each ( function() {
                   if ( $( this ).html() == 'German Market' ) {
                        $( this ).next( 'a' ).hide();
                        return false;
                    }
                });
            });

        },

        video: function() {

            $( '.wgm-video-wrapper a.open' ).click( function(){

                var video_url = $( this ).parent().find( 'span' ).html();
                var video_outer = $( this ).parent().find( '.videoouter' );
                var video_markup = '<video autoplay><scource src="' + video_url + '" type="video/mp4"></scource></video>';
                var video_div = $( this ).parent().find( '.video' );
                var video_close = $( this ).parent().find( '.close' );

                if ( $( video_div ).html() == '' ) {
                    
                    var video = $('<video />', {
                        id: 'video' + video_url,
                        src: video_url,
                        type: 'video/mp4',
                        controls: true,
                        autoplay: true,
                    });
                    
                    video.appendTo( $( video_div ) );
    
                }
                
                $(video_outer).show();
                $( this ).hide();
                $( video_close ).show();
                $( this ).parent().addClass( 'wgm-video-isShown' );
                $( video_div ).show();             

            } );

            $( '.wgm-video-wrapper a.close' ).click( function(){
               
                var video_open = $( this ).parent().parent().parent().find( '.open' );
                var video_div = $( this ).parent().find( '.video' );
                var video_outer = $( this ).parent().parent().parent().find( '.videoouter' );
                
                $( video_outer ).hide();
                $( this ).hide();
                $( video_open ).show();
                $( this ).parent().removeClass( 'wgm-video-isShown' );
                $( this ).parent().find( 'video' ).get(0).pause();
                $( video_div ).hide();  
                    
            });

            $( '.videoouter' ).click( function() {
                
                var element = $( event.target );
                
                if ( $( element ).hasClass( 'videoouter' ) ) {
                    
                    var video_open = $( this ).parent().parent().parent().find( '.open' );
                    var video_div = $( this ).parent().find( '.video' );
                    var video_outer = $( this ).parent().parent().parent().find( '.videoouter' );
                    
                    $( video_outer ).hide();
                    $( this ).hide();
                    $( video_open ).show();
                    $( this ).parent().removeClass( 'wgm-video-isShown' );
                    $( this ).parent().find( 'video' ).get(0).pause();
                    $( video_div ).hide();  
                }

            });
            
            $( '.german-market-left-menu .mobile-menu-outer' ).click( function(){
                $( '.german-market-left-menu ul,.mobile-icon' ).toggleClass( 'open' );
            });

            $( '.add-on-switcher' ).click( function() {
                $( this ).toggleClass( 'on', 'off' );
            });

        }
    };

    $( document ).ready( function( $ ) { 
        woocommerce_de.init();
    } );

} )( jQuery );
