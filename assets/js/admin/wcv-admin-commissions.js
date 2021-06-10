/* global wcv_admin_commission_params	*/
(function( $ ) {
	'use strict';
	$( '#mark_all_paid' ).click(function( e ) {
		if( ! window.confirm( wcv_admin_commissions_params.confirm_prompt ) ) {
			e.preventDefault();
		}
	});
	$( '.delete_commission' ).each( function( i, commission) {
		$(commission).on( 'click', function(e) {
			if( ! window.confirm( wcv_admin_commissions_params.confirm_delete_commission ) ){
				e.preventDefault();
			}
		});
	});

	$( '#posts-filter' ).on( 'submit', function(e) {
		 
		const action = document.getElementById( 'bulk-action-selector-top' );
		const action_value = action.value;

		if( 'delete' === action_value ) {
			if( ! window.confirm( wcv_admin_commissions_params.confirm_bulk_delete_commission ) ) {
				
				e.preventDefault();
			}
		}
	});

})( jQuery );
