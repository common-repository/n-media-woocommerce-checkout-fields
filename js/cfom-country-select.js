/** CFOM counry select for billing and shipping **/

jQuery( function( $ ) {
    /* State/Country select boxes */
	var states_json = wc_country_select_params.countries.replace( /&quot;/g, '"' ),
		states = $.parseJSON( states_json );

	$( document.body ).on( 'change', 'select.cfom_country_to_state, input.cfom_country_to_state', function() {
		// Grab wrapping element to target only stateboxes in same 'group'
		var $wrapper    = $( this ).closest('.woocommerce-billing-fields, .woocommerce-shipping-fields, .woocommerce-shipping-calculator');

		if ( ! $wrapper.length ) {
			$wrapper = $( this ).closest('.form-group').parent();
		}

		var country     = $( this ).val(),
			$statebox   = $wrapper.find( '#billing_state, #shipping_state, #calc_shipping_state' ),
			$parent     = $statebox.closest( 'p.form-group' ),
			input_name  = $statebox.attr( 'name' ),
			input_id    = $statebox.attr( 'id' ),
			value       = $statebox.val(),
			placeholder = $statebox.attr( 'placeholder' ) || $statebox.attr( 'data-placeholder' ) || '';

		
		if ( states[ country ] ) {
			if ( $.isEmptyObject( states[ country ] ) ) {

				$statebox.closest( 'p.form-group' ).hide().find( '.select2-container' ).remove();
				$statebox.replaceWith( '<input type="hidden" class="hidden" name="' + input_name + '" id="' + input_id + '" value="" placeholder="' + placeholder + '" />' );

				$( document.body ).trigger( 'country_to_state_changed', [ country, $wrapper ] );

			} else {

				var options = '',
					state = states[ country ];

				for( var index in state ) {
					if ( state.hasOwnProperty( index ) ) {
						options = options + '<option value="' + index + '">' + state[ index ] + '</option>';
					}
				}

				$statebox.closest( 'p.form-group' ).show();

				if ( $statebox.is( 'input' ) ) {
					// Change for select
					$statebox.replaceWith( '<select name="' + input_name + '" id="' + input_id + '" class="state_select" data-placeholder="' + placeholder + '"></select>' );
					$statebox = $wrapper.find( '#billing_state, #shipping_state, #calc_shipping_state' );
				}

				$statebox.html( '<option value="">' + wc_country_select_params.i18n_select_state_text + '</option>' + options );
				$statebox.val( value ).change();

				$( document.body ).trigger( 'country_to_state_changed', [country, $wrapper ] );

			}
		} else {
			if ( $statebox.is( 'select' ) ) {

				$parent.show().find( '.select2-container' ).remove();
				$statebox.replaceWith( '<input type="text" class="input-text" name="' + input_name + '" id="' + input_id + '" placeholder="' + placeholder + '" />' );

				$( document.body ).trigger( 'country_to_state_changed', [country, $wrapper ] );

			} else if ( $statebox.is( 'input[type="hidden"]' ) ) {

				$parent.show().find( '.select2-container' ).remove();
				$statebox.replaceWith( '<input type="text" class="input-text" name="' + input_name + '" id="' + input_id + '" placeholder="' + placeholder + '" />' );

				$( document.body ).trigger( 'country_to_state_changed', [country, $wrapper ] );

			}
		}

		$( document.body ).trigger( 'country_to_state_changing', [country, $wrapper ] );

	});
	
});