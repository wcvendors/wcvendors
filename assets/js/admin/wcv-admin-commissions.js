/* global wcv_admin_commission_params	*/
(function( $ ) {
	'use strict';
	$( '#mark_all_paid' ).click(function( e ) {
		if( ! window.confirm( wcv_admin_commissions_params.confirm_prompt ) ) {
			e.preventDefault();
		}
	});

})( jQuery );
