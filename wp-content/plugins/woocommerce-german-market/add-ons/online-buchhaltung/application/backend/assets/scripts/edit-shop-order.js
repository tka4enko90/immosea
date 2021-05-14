jQuery( document ).ready( function(){

	jQuery( '.online-buchhaltung-1und1-woocomerce-default' ).click( function() {

		// only if button has not been clicked before
		if ( ! ( jQuery( this ).hasClass( 'online-buchhaltung-1und1-woocomerce-x' ) || jQuery( this ).hasClass( 'online-buchhaltung-1und1-woocomerce-error' ) ) ) {
			return;
		}

		// get order id
		var order_id = jQuery( this ).attr( 'data-order-id' );

		// before doing ajax
		jQuery( this ).removeClass( 'online-buchhaltung-1und1-woocomerce-x' ).removeClass( 'online-buchhaltung-1und1-woocomerce-error dashicons dashicons-no' ).addClass( 'online-buchhaltung-1und1-woocomerce-loader' );

		// set jQuery( this ) to a variable so we can use it in jQuery.post
		var button = jQuery( this );

		// set args
		var data = {
			action: 'online_buchhaltung_1und1_edit_shop_order',
			security: online_buchhaltung_ajax.nonce,
			order_id: order_id
		};

		// refund?
		if ( jQuery( this ).attr( 'data-refund-id' ) ) {
			var refund_id = jQuery( this ).attr( 'data-refund-id' );
			data.refund_id = refund_id;
			data.action = 'online_buchhaltung_1und1_edit_shop_order_refund';
		}

		// do ajax
		jQuery.post( online_buchhaltung_ajax.url, data, function( response ) {
			
			// error handling
			if ( response != 'SUCCESS' ) {
				jQuery( button ).html( '' );
				jQuery( button ).removeClass( 'online-buchhaltung-1und1-woocomerce-loader online-buchhaltung-1und1-not-completed' ).addClass( 'online-buchhaltung-1und1-woocomerce-error dashicons dashicons-no' );
				var error_message = '<div id="message" class="error notice"><p>' + response + '</p></div>';
				jQuery( error_message ).insertAfter( '.wrap h1' ).hide().slideDown( 'fast' );

			// success handling
			} else {

				jQuery( button ).removeClass( 'online-buchhaltung-1und1-woocomerce-loader' ).addClass( 'online-buchhaltung-1und1-woocomerce-yes' );

			}

			console.log( response );

		} );

	});

});
